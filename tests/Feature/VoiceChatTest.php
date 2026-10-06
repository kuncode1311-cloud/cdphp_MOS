<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VoiceAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Trò chuyện bằng giọng nói với Trợ lý AI: chỉ người có gói Trợ lý AI còn hạn, có giới hạn lượt/ngày, câu trả lời được làm sạch để đọc to.
 */
class VoiceChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.question_ai.base_url' => 'https://router.test/v1',
            'services.question_ai.model' => 'ag/gemini-test',
            'services.question_ai.api_key' => 'k',
            'voice.daily_turns' => 3,
        ]);
    }

    private function student(bool $withAi = true): User
    {
        $this->seed();

        return User::factory()->create([
            'role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(30), 'max_students' => 1,
            'ai_assistant_until' => $withAi ? now()->addDays(10) : null,
        ]);
    }

    private function fakeAi(string $text): void
    {
        Http::fake(['router.test/*' => Http::response(['choices' => [['message' => ['content' => $text]]]])]);
    }

    private function wav(): string
    {
        // Tệp WAV tối thiểu hợp lệ (44 byte đầu + một ít dữ liệu)
        return 'RIFF' . pack('V', 36 + 100) . 'WAVEfmt ' . pack('VvvVVvv', 16, 1, 1, 16000, 32000, 2, 16) . 'data' . pack('V', 100) . str_repeat("\0", 100);
    }

    public function test_reply_requires_active_ai_package(): void
    {
        $student = $this->student(false);

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'Chào cô'])
            ->assertStatus(403)
            ->assertJson(['ok' => false]);
    }

    public function test_guest_cannot_use_voice_endpoints(): void
    {
        $this->postJson(route('voice.reply'), ['text' => 'Chào'])->assertStatus(401);
    }

    public function test_reply_returns_clean_speakable_text(): void
    {
        $student = $this->student();
        $this->fakeAi("**Chào em!** 😊 Hôm nay em muốn luyện bài nào?\n\n- Bài một\n- Xem [tại đây](https://mos.app/x)");

        $res = $this->actingAs($student)->postJson(route('voice.reply'), [
            'text' => 'Chào cô',
            'history' => [['role' => 'user', 'text' => 'Hi'], ['role' => 'assistant', 'text' => 'Chào em']],
        ])->assertOk()->assertJson(['ok' => true]);

        $text = $res->json('text');
        $this->assertStringContainsString('Chào em', $text);
        $this->assertStringNotContainsString('*', $text);
        $this->assertStringNotContainsString('😊', $text);
        $this->assertStringNotContainsString('https://', $text);
        $this->assertStringNotContainsString("\n", $text);
    }

    public function test_reply_prompt_includes_only_own_learning_data_and_voice_rules(): void
    {
        $student = $this->student();
        $this->fakeAi('Dạ em nhé.');

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'Em nên học thêm gì để giỏi hơn?'])->assertOk();

        Http::assertSent(function ($request) use ($student) {
            $system = $request['messages'][0]['content'] ?? '';

            return str_contains($system, 'đọc to')
                && str_contains($system, $student->name)
                && ($request['messages'][1]['content'] ?? '') === 'Em nên học thêm gì để giỏi hơn?';
        });
    }

    public function test_daily_turn_limit_is_enforced(): void
    {
        $student = $this->student();
        $this->fakeAi('Dạ.');

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($student)->postJson(route('voice.reply'), ['text' => "Câu {$i}"])->assertOk();
        }

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'Câu nữa'])
            ->assertStatus(429)
            ->assertJson(['ok' => false]);
    }

    public function test_ai_failure_returns_friendly_503(): void
    {
        $student = $this->student();
        Http::fake(['*' => Http::response('loi', 500)]);

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'Máy tính là gì vậy cô?'])
            ->assertStatus(503)
            ->assertJson(['ok' => false]);
    }

    public function test_reply_validates_input(): void
    {
        $student = $this->student();

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => ''])->assertStatus(422);
        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => str_repeat('a', 600)])->assertStatus(422);
        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'ok', 'history' => [['role' => 'system', 'text' => 'x']]])->assertStatus(422);
    }

    public function test_transcribe_returns_text_from_audio(): void
    {
        $student = $this->student();
        $this->fakeAi('Em muốn luyện bài một');
        $file = UploadedFile::fake()->createWithContent('nghe.wav', $this->wav());

        $this->actingAs($student)->post(route('voice.transcribe'), ['audio' => $file], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['ok' => true, 'text' => 'Em muốn luyện bài một']);
    }

    public function test_transcribe_rejects_non_wav_and_missing_ai(): void
    {
        $student = $this->student();
        $fake = UploadedFile::fake()->createWithContent('x.wav', 'khong-phai-wav-' . str_repeat('x', 60));

        $this->actingAs($student)->post(route('voice.transcribe'), ['audio' => $fake], ['Accept' => 'application/json'])->assertStatus(422);

        $noAi = $this->student(false);
        $this->actingAs($noAi)->post(route('voice.transcribe'), ['audio' => UploadedFile::fake()->createWithContent('n.wav', $this->wav())], ['Accept' => 'application/json'])->assertStatus(403);
    }

    public function test_transcribe_treats_silence_marker_as_no_speech(): void
    {
        $student = $this->student();
        $this->fakeAi('KHONG');

        $this->actingAs($student)->post(route('voice.transcribe'), ['audio' => UploadedFile::fake()->createWithContent('n.wav', $this->wav())], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['ok' => false, 'text' => '']);
    }

    public function test_clean_for_speech_helper(): void
    {
        $voice = app(VoiceAssistantService::class);

        $this->assertSame('Xin chào em. Em khỏe không?', $voice->cleanForSpeech("**Xin chào** em.\nEm khỏe không? 🎉"));
    }

    public function test_web_addresses_are_never_read_aloud(): void
    {
        $voice = app(VoiceAssistantService::class);

        $clean = $voice->cleanForSpeech('Em hãy vào mos.app/bang-gia để mua gói, hoặc xem https://mos.app/x và /bang-gia nhé.');

        $this->assertStringNotContainsString('mos.app', $clean);
        $this->assertStringNotContainsString('bang-gia', $clean);
        $this->assertStringNotContainsString('https', $clean);
        $this->assertStringContainsString('Em hãy vào', $clean);
        $this->assertStringContainsString('để mua gói', $clean);
    }

    public function test_ai_can_offer_a_button_to_open_a_page_and_marker_is_hidden(): void
    {
        $student = $this->student();
        $this->fakeAi("Cô đưa em nút mở trang nhé.
[[MO: bang_gia]]");

        $res = $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'em muốn mua gói'])->assertOk();

        $this->assertStringNotContainsString('MO:', $res->json('text'));
        $this->assertSame(route('pricing.index'), $res->json('action.url'));
        $this->assertFalse($res->json('action.auto'));
    }

    public function test_page_opens_automatically_only_when_student_clearly_asks_to_open_it(): void
    {
        $student = $this->student();
        $this->fakeAi("Dạ cô mở liền.
[[MO_NGAY: thanh_tich]]");

        $res = $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'mở trang thành tích giúp em'])->assertOk();

        $this->assertTrue($res->json('action.auto'));
    }

    public function test_unknown_action_code_is_ignored(): void
    {
        $student = $this->student();
        $this->fakeAi("Dạ.
[[MO: trang_la_hoac_xau]]");

        $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'mở trang đó'])->assertOk()->assertJson(['action' => null]);
    }

    public function test_leaked_english_reasoning_is_not_shown_or_spoken(): void
    {
        $student = $this->student();
        $this->fakeAi('User said "lúc đầu". In context: this is vague/unclear. Rule: keep it short. Natural tone. Friendly AI teacher.Ý em là lúc đầu thế nào, em nói rõ hơn nhé.');

        $res = $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'lúc đầu'])->assertOk();

        $this->assertStringNotContainsString('User said', $res->json('text'));
        $this->assertStringNotContainsString('Natural tone', $res->json('text'));
        $this->assertStringContainsString('Ý em là lúc đầu thế nào', $res->json('text'));
    }

    public function test_when_ai_returns_only_english_reasoning_student_gets_a_friendly_reask(): void
    {
        $student = $this->student();
        $this->fakeAi('The user said hello. This is a short friendly reply with a natural tone.');

        $res = $this->actingAs($student)->postJson(route('voice.reply'), ['text' => 'cái đó là sao'])->assertOk();

        $this->assertSame('Em nói rõ hơn một chút cho cô nghe với nhé!', $res->json('text'));
    }

    public function test_voice_buttons_show_for_ai_member_and_upgrade_for_others(): void
    {
        $withAi = $this->student(true);
        $this->actingAs($withAi)->get(route('home'))
            ->assertOk()
            ->assertSee('Trò chuyện AI')
            ->assertSee('id="vc-overlay"', false);

        $withoutAi = $this->student(false);
        $this->actingAs($withoutAi)->get(route('home'))
            ->assertOk()
            ->assertSee('Nâng cấp AI');

        Cache::flush();
    }
}
