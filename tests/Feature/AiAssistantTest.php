<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PracticeTest;
use App\Models\SupportMessage;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Trợ lý AI nâng cao: đọc dữ liệu đúng vai trò, chọn hành động điều hướng hợp lệ, và kích hoạt gói AI.
 */
class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => '']);
        config([
            'services.question_ai.base_url' => 'https://9router.test/v1',
            'services.question_ai.model' => 'ag/gemini-3.8-flash-low',
        ]);
        Cache::flush();
        $this->seed();
    }

    /** Gửi tin nhắn kênh AI với phản hồi giả lập của mô hình */
    private function ask(?User $user, string $message, string $modelContent): array
    {
        Http::fake(['9router.test/*' => Http::response(['choices' => [['message' => ['content' => $modelContent]]]])]);

        if ($user) {
            $this->actingAs($user);
            $name = $user->name;
        } else {
            $name = 'Khách';
        }

        return $this->postJson(route('support.message.send'), [
            'name' => $name, 'message' => $message, 'channel' => 'ai',
        ])->assertOk()->json();
    }

    /** Nội dung system prompt của lần gọi mô hình gần nhất */
    private function sentSystemPrompt(): string
    {
        $recorded = Http::recorded(fn ($request) => str_contains($request->url(), '9router.test'));

        return (string) ($recorded->last()[0]['messages'][0]['content'] ?? '');
    }

    /** Tạo thành viên; hạn Trợ lý AI đặt riêng vì không nằm trong danh sách gán hàng loạt */
    private function member(string $role = 'student', array $extra = []): User
    {
        $aiUntil = array_key_exists('ai_assistant_until', $extra) ? $extra['ai_assistant_until'] : now()->addMonth();
        unset($extra['ai_assistant_until']);

        $user = User::factory()->create(array_merge(['role' => $role], $extra));
        $user->forceFill(['ai_assistant_until' => $aiUntil])->save();

        return $user;
    }

    /** Bài luyện đã xuất bản thuộc một chủ đề có khối lớp, đầu danh sách để nằm trong 25 bài trợ lý được chọn */
    private function publishedTest(): PracticeTest
    {
        return PracticeTest::where('is_published', true)->where('is_mock', false)
            ->whereHas('topic', fn ($t) => $t->whereNotNull('level_id'))
            ->orderBy('position')->firstOrFail();
    }

    /** Khối lớp của bài luyện: lấy từ chủ đề, giống cách màn học tập xác định */
    private function levelIdOf(PracticeTest $test): int
    {
        return (int) $test->topic->level_id;
    }

    // =========================================================================
    // 🔐 QUYỀN TRUY CẬP
    // =========================================================================

    public function test_chua_co_goi_ai_thi_hoi_ket_qua_bi_nhac_mua_goi(): void
    {
        $student = $this->member('student', ['ai_assistant_until' => null]);

        $data = $this->ask($student, 'Tôi đã làm bài nào rồi?', '{"reply":"x","action":null}');

        $this->assertStringContainsString('gói Trợ lý AI', $data['bot_replies'][0]);
        $this->assertSame(route('pricing.index'), $data['bot_action']['url']);
        // Không có quyền thì không được gọi mô hình để tra dữ liệu
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '9router.test'));
    }

    public function test_khach_vang_lai_hoi_ket_qua_duoc_nhac_dang_nhap(): void
    {
        $data = $this->ask(null, 'Con tôi làm bài nào rồi?', '{"reply":"x","action":null}');

        $this->assertStringContainsString('đăng nhập', $data['bot_replies'][0]);
        $this->assertSame(route('login'), $data['bot_action']['url']);
        $this->assertSame(0, SupportMessage::count());
    }

    // =========================================================================
    // 🧩 DỮ LIỆU THEO VAI TRÒ
    // =========================================================================

    public function test_hoc_sinh_chi_thay_ket_qua_cua_chinh_minh(): void
    {
        $test = $this->publishedTest();
        $mine = $this->member('student', ['name' => 'Bé An']);
        $other = $this->member('student', ['name' => 'Bạn Bình']);
        TestAttempt::create(['user_id' => $mine->id, 'practice_test_id' => $test->id, 'score' => 850, 'correct_answers' => 9, 'total_questions' => 10, 'completed_at' => now()]);
        TestAttempt::create(['user_id' => $other->id, 'practice_test_id' => PracticeTest::where('id', '!=', $test->id)->value('id'), 'score' => 300, 'correct_answers' => 3, 'total_questions' => 10, 'completed_at' => now()]);

        $this->ask($mine, 'Kết quả của tôi thế nào?', '{"reply":"Bạn đạt điểm cao.","action":null}');
        $prompt = $this->sentSystemPrompt();

        $this->assertStringContainsString('"diem": 850', $prompt);
        $this->assertStringNotContainsString('Bạn Bình', $prompt);
        $this->assertStringNotContainsString('"diem": 300', $prompt);
    }

    public function test_giao_vien_chi_thay_hoc_sinh_cua_minh(): void
    {
        $teacherA = $this->member('teacher');
        $teacherB = $this->member('teacher');
        $myStudent = $this->member('student', ['name' => 'Học Sinh Lớp A', 'created_by' => $teacherA->id]);
        $otherStudent = $this->member('student', ['name' => 'Học Sinh Lớp B', 'created_by' => $teacherB->id]);

        $this->ask($teacherA, 'Học sinh của tôi làm được bao nhiêu bài?', '{"reply":"x","action":null}');
        $prompt = $this->sentSystemPrompt();

        $this->assertStringContainsString($myStudent->name, $prompt);
        $this->assertStringNotContainsString($otherStudent->name, $prompt);
    }

    // =========================================================================
    // 🎯 HÀNH ĐỘNG ĐIỀU HƯỚNG
    // =========================================================================

    public function test_hanh_dong_hop_le_duoc_chon_va_co_duong_dan_do_may_chu_tao(): void
    {
        $student = $this->member('student');
        $test = $this->publishedTest();
        $student->accessibleLevels()->attach($this->levelIdOf($test));

        $data = $this->ask($student, 'Cho tôi làm bài này', json_encode(['reply' => 'Mình mở bài cho bạn nhé!', 'action' => 'lam_' . $test->id]));

        $this->assertSame('Mình mở bài cho bạn nhé!', $data['bot_replies'][0]);
        $this->assertSame(route('tests.launch', $test), $data['bot_action']['url']);
    }

    public function test_hanh_dong_khong_co_trong_danh_sach_bi_loai_bo(): void
    {
        $student = $this->member('student');

        // AI tự bịa mã hành động hoặc đường dẫn: máy chủ bỏ qua, không mở trang lạ
        $data = $this->ask($student, 'Mở trang lạ', json_encode(['reply' => 'Đây nhé.', 'action' => 'https://evil.test/login']));

        $this->assertSame('Đây nhé.', $data['bot_replies'][0]);
        $this->assertNull($data['bot_action']);
    }

    public function test_tra_loi_khong_phai_json_van_hien_thi_noi_dung(): void
    {
        $student = $this->member('student');

        $data = $this->ask($student, 'Xin chào', 'Chào bạn, mình có thể giúp gì?');

        $this->assertSame('Chào bạn, mình có thể giúp gì?', $data['bot_replies'][0]);
        $this->assertNull($data['bot_action']);
    }

    public function test_nut_hanh_dong_duoc_luu_cung_luot_tra_loi(): void
    {
        $student = $this->member('student');
        $this->actingAs($student);
        Http::fake(['9router.test/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['reply' => 'Xem gói nhé', 'action' => 'bang_gia'])]]]])]);

        $data = $this->postJson(route('support.message.send'), ['name' => $student->name, 'message' => 'Mở trang gói', 'channel' => 'ai'])->json();

        $msg = SupportMessage::findOrFail($data['message_id']);
        $saved = collect($msg->conversation_history)->last();
        $this->assertSame('bot', $saved['sender']);
        $this->assertSame(route('pricing.index'), $saved['action']['url']);
    }

    // =========================================================================
    // 💳 KÍCH HOẠT GÓI TRỢ LÝ AI
    // =========================================================================

    public function test_thanh_toan_goi_tro_ly_ai_chi_cong_them_quyen_ai_khong_doi_han_hoc_tap(): void
    {
        $student = $this->member('student', ['ai_assistant_until' => null, 'expires_at' => now()->addDays(10)->toDateString()]);
        $package = Package::create([
            'slug' => 'tro-ly-ai-hoc-sinh', 'name' => 'Trợ lý AI', 'target_audience' => 'student',
            'price' => 49000, 'duration_days' => 30, 'max_students' => 1, 'is_active' => true, 'grants_ai_assistant' => true,
        ]);
        $expiryBefore = $student->fresh()->expires_at->toDateString();

        $service = app(SubscriptionService::class);
        $order = $service->createOrder($student, $package);
        $this->assertTrue($service->activateOrder($order));

        $student->refresh();
        $this->assertTrue($student->hasAiAssistant());
        $this->assertSame($expiryBefore, $student->expires_at->toDateString(), 'Hạn học tập không được đổi');
        $this->assertSame('active', $order->fresh()->status);
    }
}
