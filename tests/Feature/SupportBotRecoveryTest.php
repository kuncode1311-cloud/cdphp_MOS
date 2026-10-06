<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\SupportBotService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Trợ lý AI trong khung chat hỗ trợ: trả lời khi Admin offline và khôi phục mật khẩu qua OTP email.
 */
class SupportBotRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => 'test-key']);
        // Mặc định tắt 9Router trong test để không gọi mạng thật; test riêng sẽ bật lại
        config(['services.question_ai.base_url' => '', 'services.question_ai.model' => '']);
        Cache::flush();
    }

    public function test_9router_tra_ve_luong_sse_van_doc_duoc_noi_dung(): void
    {
        config([
            'services.question_ai.base_url' => 'https://9router.test/v1',
            'services.question_ai.model' => 'ag/gemini-3.8-flash-low',
        ]);
        $sse = "data: {\"choices\":[{\"delta\":{\"content\":\"Xin \"}}]}\n\n"
            . "data: {\"choices\":[{\"delta\":{\"content\":\"chào\"}}]}\n\n"
            . "data: [DONE]\n\n";
        Http::fake(['9router.test/*' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream'])]);

        $data = $this->send('Xin chào');

        $this->assertSame(['Xin chào'], $data['bot_replies']);
    }

    public function test_prompt_chua_tai_lieu_faq_va_goi_tu_csdl(): void
    {
        config([
            'services.question_ai.base_url' => 'https://9router.test/v1',
            'services.question_ai.model' => 'ag/gemini-3.8-flash-low',
        ]);
        \App\Models\Package::create([
            'slug' => 'goi-thu-nghiem-la',
            'name' => 'Gói Thử Nghiệm Lạ',
            'target_audience' => 'student',
            'price' => 123000,
            'duration_days' => 77,
            'max_students' => 1,
            'is_active' => true,
        ]);
        Http::fake(['9router.test/*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]])]);

        $this->send('Gói nào rẻ nhất?');

        Http::assertSent(function ($request) {
            $system = $request['messages'][0]['content'] ?? '';

            return str_contains($system, '=== TÀI LIỆU HỖ TRỢ ===')
                && str_contains($system, 'quên mật khẩu')
                && str_contains($system, 'Gói Thử Nghiệm Lạ: 123.000 đ, dùng 77 ngày');
        });
    }

    public function test_ai_uu_tien_9router_va_dung_model_cau_hinh(): void
    {
        config([
            'services.question_ai.base_url' => 'https://9router.test/v1',
            'services.question_ai.model' => 'ag/gemini-3.8-flash-low',
            'services.question_ai.api_key' => 'router-key',
        ]);
        Http::fake(['9router.test/*' => Http::response([
            'choices' => [['message' => ['content' => 'Trả lời từ 9Router']]],
        ])]);

        $data = $this->send('Tài liệu IC3 có những phần nào?');

        $this->assertSame(['Trả lời từ 9Router'], $data['bot_replies']);
        Http::assertSent(fn ($request) => str_contains($request->url(), '9router.test/v1/chat/completions')
            && $request['model'] === 'ag/gemini-3.8-flash-low');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'generativelanguage'));
    }

    private function send(string $message, array $extra = []): array
    {
        return $this->postJson(route('support.message.send'), array_merge([
            'name' => 'Khách thử',
            'contact' => '0912345678',
            'phone' => '0912345678',
            'message' => $message,
            'channel' => 'ai', // Trợ lý AI là kênh riêng, mặc định các test này gửi vào kênh AI
        ], $extra))->assertOk()->json();
    }

    public function test_ai_tra_loi_khi_admin_chua_online(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Mình gợi ý bạn xem các gói bản quyền nhé.']]]]],
        ])]);

        $data = $this->send('Gói bản quyền giá bao nhiêu?');

        $this->assertSame(['Mình gợi ý bạn xem các gói bản quyền nhé.'], $data['bot_replies']);
        $this->assertFalse($data['secret_next']);
        // Khách vãng lai không được lưu vào CSDL
        $this->assertNull($data['message_id']);
        $this->assertSame(0, SupportMessage::count());
    }

    /** Cùng một trình duyệt: cookie phiên cố định cho các request liên tiếp trong test */
    private function newBrowser(): void
    {
        app('session.store')->flush();
        $this->withCookie(config('session.cookie'), app('encrypter')->encrypt(\Illuminate\Support\Str::random(40), false));
    }

    public function test_kenh_ban_quan_tri_khong_co_ai_tra_loi(): void
    {
        // Dù Admin offline, tin ở kênh Ban Quản Trị vẫn chỉ chờ Admin, AI không trả lời
        Http::fake();
        $member = User::factory()->create(['role' => 'student']);

        $data = $this->actingAs($member)->postJson(route('support.message.send'), [
            'name' => 'Khách', 'contact' => '0912345678', 'message' => 'Cho mình hỏi về tài liệu thi', 'channel' => 'admin',
        ])->assertOk()->json();

        $this->assertSame('admin', $data['channel']);
        $this->assertSame([], $data['bot_replies']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'generativelanguage'));
    }

    public function test_kenh_ai_van_tra_loi_khi_admin_dang_online(): void
    {
        SupportBotService::markAdminActive();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Trả lời từ AI']]]]],
        ])]);

        $data = $this->send('Tài liệu IC3 có gì?');

        $this->assertSame(['Trả lời từ AI'], $data['bot_replies']);
        $this->assertTrue($data['admin_online']);
    }

    public function test_khach_vang_lai_yeu_cau_gap_nguoi_that_thi_duoc_nhac_dang_nhap(): void
    {
        Http::fake();

        $data = $this->send('Cho mình nói chuyện với nhân viên');

        $this->assertStringContainsString('đăng nhập', $data['bot_replies'][0]);
        $this->assertSame(0, SupportMessage::count());
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'generativelanguage'));
    }

    public function test_thanh_vien_yeu_cau_gap_nhan_vien_thi_ai_im_lang_trong_cuoc_tro_chuyen(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'AI trả lời']]]]],
        ])]);
        $member = User::factory()->create(['role' => 'student']);

        $first = $this->actingAs($member)->postJson(route('support.message.send'), [
            'name' => $member->name, 'contact' => '0912345678', 'message' => 'Cho mình nói chuyện với nhân viên', 'channel' => 'ai',
        ])->assertOk()->json();
        $this->assertStringContainsString('chuyển yêu cầu', $first['bot_replies'][0]);

        // Tin sau đó: AI không trả lời nữa, chỉ nhắc chuyển sang tab Ban Quản Trị
        $second = $this->actingAs($member)->postJson(route('support.message.send'), [
            'name' => $member->name, 'message' => 'Gói Tiêu Chuẩn giá bao nhiêu?', 'channel' => 'ai',
            'parent_id' => $first['message_id'],
        ])->assertOk()->json();

        $this->assertStringContainsString('tab 👨‍💼 Ban Quản Trị', $second['bot_replies'][0]);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'generativelanguage') && str_contains(json_encode($request->data()), 'giá bao nhiêu'));
    }


    public function test_ai_gioi_han_theo_cuoc_tro_chuyen_de_chong_spam(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'AI trả lời']]]]],
        ])]);
        // Thành viên có cuộc trò chuyện được lưu nên giới hạn theo cuộc ổn định (khách vãng lai giới hạn theo phiên trình duyệt)
        $member = User::factory()->create(['role' => 'student']);

        $parent = null;
        $lastReply = '';
        for ($i = 1; $i <= 12; $i++) {
            $data = $this->actingAs($member)->postJson(route('support.message.send'), [
                'name' => 'Học sinh', 'message' => "Câu hỏi số {$i}", 'channel' => 'ai', 'parent_id' => $parent,
            ])->assertOk()->json();
            $parent = $data['message_id'];
            $lastReply = $data['bot_replies'][0] ?? '';
        }

        // 10 lượt đầu được AI trả lời, từ lượt 11 trở đi bị giới hạn (không gọi AI nữa)
        $aiCalls = collect(Http::recorded())->filter(fn ($pair) => str_contains($pair[0]->url(), 'generativelanguage'))->count();
        $this->assertSame(10, $aiCalls);
        $this->assertStringContainsString('hỏi khá nhiều', $lastReply);
    }

    public function test_nhan_dien_cach_go_sai_quen_mat_khau(): void
    {
        $bot = app(SupportBotService::class);
        $request = request();

        foreach (["qên pass'", 'tôi ko nhớ pas', 'quên mật khẩu', 'Mình quên mk', 'bị mất pass rồi'] as $text) {
            $this->assertTrue($bot->wantsRecovery($request, $text), "Không nhận ra: {$text}");
        }
        foreach (['Gói bản quyền giá bao nhiêu?', 'Mình quen thầy Hùng', 'Cho mình hỏi tài liệu thi'] as $text) {
            $this->assertFalse($bot->wantsRecovery($request, $text), "Nhận nhầm: {$text}");
        }
    }

    public function test_khoi_phuc_mat_khau_day_du_va_khong_luu_otp_hay_mat_khau(): void
    {
        SupportBotService::markAdminActive(); // Admin online: chỉ luồng khôi phục mới được bot xử lý
        Mail::fake();
        $user = User::factory()->create(['email' => 'hocsinh@gmail.com', 'password' => Hash::make('cu-mat-khau-123')]);
        $this->assertNotNull($user);

        // 1. Bắt đầu khôi phục (không cần SĐT)
        $data = $this->send('Mình quên mật khẩu rồi', ['phone' => null, 'contact' => '']);
        $this->assertTrue($data['flow_active']);
        $this->assertStringContainsString('email', $data['bot_replies'][0]);

        // 2. Nhập Email đăng nhập -> bot chỉ hiện email đã che
        $data = $this->send('hocsinh@gmail.com', ['phone' => null, 'contact' => '']);
        // Che phần đầu email, giữ nguyên tên miền
        $this->assertStringContainsString('ho*****@gmail.com', $data['bot_replies'][0]);
        $this->assertStringNotContainsString('hocsinh@', $data['bot_replies'][0]);

        // 3. Nhập sai email xác nhận -> không gửi OTP
        $data = $this->send('sai@gmail.com', ['phone' => null, 'contact' => '']);
        Mail::assertNothingSent();

        // 4. Nhập đúng email -> gửi OTP, bước tiếp theo được che
        $this->send('hocsinh@gmail.com', ['phone' => null, 'contact' => '']);
        $otp = '';
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return $mail->hasTo('hocsinh@gmail.com');
        });
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        // 5. OTP sai -> báo lỗi, vẫn ở bước OTP
        $wrong = $otp === '000000' ? '111111' : '000000';
        $data = $this->send($wrong, ['phone' => null, 'contact' => '']);
        $this->assertTrue($data['secret_next']);

        // 6. OTP đúng -> bước đặt mật khẩu mới
        $data = $this->send($otp, ['phone' => null, 'contact' => '']);
        $this->assertTrue($data['secret_next']);

        // 7. Mật khẩu mới -> đổi thành công
        $data = $this->send('matkhau-moi-456', ['phone' => null, 'contact' => '']);
        $this->assertFalse($data['flow_active']);
        $this->assertTrue(Hash::check('matkhau-moi-456', $user->fresh()->password));

        // Khách vãng lai không được lưu hội thoại nên OTP và mật khẩu không nằm ở bất kỳ đâu trong CSDL
        $this->assertSame(0, SupportMessage::count());
        $this->assertStringNotContainsString($otp, json_encode($data));
    }
}
