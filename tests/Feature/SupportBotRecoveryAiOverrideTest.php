<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use App\Services\AiChatClient;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * AI phân loại ý định trong luồng khôi phục mật khẩu (decideRecoveryAction) đôi khi đoán sai,
 * ví dụ xem một họ tên thật đã gõ rõ ràng là "chưa trả lời" rồi hỏi lại chung chung thay vì tra
 * CSDL. Bộ test này giả lập AI LUÔN đoán sai để đảm bảo: khi người dùng đã gõ một họ tên đầy đủ
 * rõ ràng (>=3 từ) ở bước tìm tài khoản, hệ thống luôn tra thẳng CSDL (không phụ thuộc AI).
 */
class SupportBotRecoveryAiOverrideTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => 'test-key', 'services.question_ai.base_url' => 'https://fake.test', 'services.question_ai.model' => 'fake-model', 'mail.default' => 'smtp']);
        Cache::flush();
        Mail::fake();
        Http::fake();
        Cache::forget('support_admin_seen_at');

        $this->teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Cô Mai Linh (GV 3A1)']);

        // Giả lập AI phân loại ý định: nhận đúng "không nhớ" (để vào được bước tìm tài khoản như
        // luồng thật), nhưng với MỌI input khác (kể cả một họ tên thật rõ ràng) thì LUÔN đoán sai
        // là "chưa trả lời" — đúng tình huống lỗi thực tế đã gặp. complete() cũng luôn thất bại (null).
        $this->mock(AiChatClient::class, function ($mock) {
            $mock->shouldReceive('chatWithTools')->andReturnUsing(function (array $messages) {
                $userContent = '';
                foreach ($messages as $m) {
                    if (($m['role'] ?? '') === 'user') {
                        $userContent = (string) ($m['content'] ?? '');
                    }
                }
                $decoded = json_decode($userContent, true) ?? [];
                $userMessage = \App\Support\VietText::norm((string) ($decoded['user_message'] ?? ''));
                $action = str_contains($userMessage, 'khong nho') ? 'no_recall' : 'not_answer';

                return ['tool_calls' => [['name' => 'choose_recovery_action', 'arguments' => ['action' => $action]]]];
            });
            $mock->shouldReceive('complete')->andReturn(null);
        });
    }

    private function student(string $name, string $email, string $class = 'Lớp 3A1'): User
    {
        $classroom = Classroom::firstOrCreate(['name' => $class], ['grade' => 3, 'teacher_id' => $this->teacher->id]);

        return User::factory()->create([
            'role' => 'student', 'name' => $name, 'email' => $email, 'student_code' => 'HS' . random_int(100, 999),
            'classroom_id' => $classroom->id, 'created_by' => $this->teacher->id,
        ]);
    }

    private function say(string $message): array
    {
        return $this->postJson(route('support.message.send'), [
            'name' => 'Khách', 'contact' => '0912345678', 'message' => $message, 'channel' => 'ai',
        ])->assertOk()->json();
    }

    private function replies(array $data): string
    {
        return implode("\n", $data['bot_replies']);
    }

    public function test_ho_ten_that_van_tim_duoc_tai_khoan_du_ai_doan_sai_la_chua_tra_loi(): void
    {
        $this->student('Nguyễn An Nhiên', 'annhien.real@gmail.com');

        $this->say('quên mật khẩu');
        $this->say('không nhớ');
        $text = $this->replies($this->say('Nguyễn An Nhiên'));

        // Dù AI (bị giả lập) luôn trả lời "chưa trả lời", code vẫn phải tra đúng CSDL và gợi ý
        // tài khoản gần giống, thay vì hỏi lại chung chung không có thông tin hữu ích.
        $this->assertStringContainsString('gần giống', $text);
        $this->assertStringContainsString('Nguyễn An Nhiên', $text);
        $this->assertStringContainsString('lớp', mb_strtolower($text));
    }

    public function test_ho_ten_khong_dau_viet_thuong_van_tim_duoc_du_ai_doan_sai(): void
    {
        $this->student('Lê Thảo My', 'thaomy.test@gmail.com');

        $this->say('quên mật khẩu');
        $this->say('không nhớ');
        $text = $this->replies($this->say('le thao my'));

        $this->assertStringContainsString('gần giống', $text);
    }

    public function test_ten_qua_ngan_mo_ho_van_qua_ai_va_co_cau_hoi_lai_du_phong(): void
    {
        $this->say('quên mật khẩu');
        $this->say('không nhớ');
        // "Tuệ Mẫn" chỉ 2 từ, không đủ rõ là họ tên đầy đủ nên vẫn đi qua AI (đang bị giả lập đoán sai).
        $data = $this->say('Tuệ Mẫn');

        // AI giả lập luôn trả "not_answer" và complete() trả null: code phải có câu hỏi lại dự phòng
        // hợp lý (không được để trống / lỗi), và không được bịa ra việc đã tìm thấy tài khoản.
        $this->assertNotEmpty($data['bot_replies']);
        $this->assertStringNotContainsString('gần giống', $this->replies($data));
    }
}
