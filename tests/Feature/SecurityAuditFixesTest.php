<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\PracticeTest;
use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử các lỗ hổng đã được vá sau đợt rà soát bảo mật:
 * phân quyền giáo viên, thanh toán PayOS, chat hỗ trợ, chấm sao, sổ câu sai, cấu hình Telegram.
 */
class SecurityAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::where('email', 'teacher@ic3.test')->firstOrFail();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    private function student(): User
    {
        return User::where('role', 'student')->firstOrFail();
    }

    private function pendingOrderFor(User $user): PackageOrder
    {
        return app(SubscriptionService::class)->createOrder($user, Package::firstOrFail(), [
            'payment_method' => 'payos',
        ]);
    }

    /** Tạo một câu trắc nghiệm có một đáp án đúng ở vị trí 0 trong bộ đề mẫu */
    private function practiceQuestion(PracticeTest $test): Question
    {
        $question = $test->questions()->create([
            'type' => 'MultipleChoice',
            'title' => 'Câu kiểm thử bảo mật',
            'configuration' => [],
            'position' => $test->questions()->count(),
            'points' => 1,
            'is_published' => true,
        ]);
        $question->options()->create(['content' => 'Đúng', 'is_correct' => true, 'position' => 0, 'metadata' => []]);
        $question->options()->create(['content' => 'Sai', 'is_correct' => false, 'position' => 1, 'metadata' => []]);

        return $question;
    }

    public function test_giao_vien_khong_vao_duoc_chuc_nang_trung_tam(): void
    {
        $this->seed();
        $teacher = $this->teacher();
        $order = $this->pendingOrderFor($teacher);

        $this->actingAs($teacher)->post(route('admin.orders.activate', $order))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.orders.reject', $order))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.support.poll'))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.telegram.save'), ['bot_token' => '123456:abcdefghijklmnopqrstuvwxyz'])->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.games.settings'))->assertForbidden();

        $this->assertSame(PackageOrder::STATUS_PENDING, $order->fresh()->status);

        // Giáo viên vẫn dùng được Khu quản trị của lớp mình
        $this->actingAs($teacher)->get(route('admin.overview'))->assertOk();
    }

    public function test_admin_tong_van_duyet_duoc_don(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        $this->actingAs($this->admin())->post(route('admin.orders.activate', $order))->assertRedirect();

        $this->assertSame(PackageOrder::STATUS_ACTIVE, $order->fresh()->status);
    }

    public function test_tham_so_status_paid_tren_url_khong_kich_hoat_don(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        $this->get(route('pricing.payos.return', $order) . '?status=PAID&code=00')->assertRedirect();

        $this->assertSame(PackageOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_webhook_payos_sai_so_tien_khong_kich_hoat(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        $this->mock(PayosService::class, function ($mock) use ($order) {
            $mock->shouldReceive('verifyWebhookData')->andReturn([
                'orderCode' => (int) ($order->id . '123456'),
                'amount' => (int) $order->price - 1000,
                'description' => $order->code,
            ]);
        });

        $this->postJson(route('pricing.payos.webhook'), [])->assertStatus(422);

        $this->assertSame(PackageOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_webhook_payos_dung_so_tien_kich_hoat_don(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        $this->mock(PayosService::class, function ($mock) use ($order) {
            $mock->shouldReceive('verifyWebhookData')->andReturn([
                'orderCode' => (int) ($order->id . '123456'),
                'amount' => (int) $order->price,
                'description' => $order->code,
            ]);
        });
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendPaymentSuccessNotification')->andReturn(true);
        });

        $this->postJson(route('pricing.payos.webhook'), [])->assertOk();

        $this->assertSame(PackageOrder::STATUS_ACTIVE, $order->fresh()->status);
    }

    public function test_webhook_mo_ta_ngan_khong_khop_nham_don_khac(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        // Nội dung chuyển khoản chỉ là "MOS" (chuỗi ngắn) thì không được khớp với mã đơn nào
        $this->mock(PayosService::class, function ($mock) use ($order) {
            $mock->shouldReceive('verifyWebhookData')->andReturn([
                'orderCode' => 1,
                'amount' => (int) $order->price,
                'description' => 'MOS',
            ]);
        });

        $this->postJson(route('pricing.payos.webhook'), [])->assertOk();

        $this->assertSame(PackageOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_khach_chi_biet_ma_don_khong_duoc_tu_dang_nhap(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());
        $order->update(['status' => PackageOrder::STATUS_ACTIVE]);

        $this->flushSession();
        $this->getJson(route('pricing.order.status', $order))->assertOk();

        $this->assertGuest();
    }

    public function test_khach_khac_khong_doc_duoc_hoi_thoai_chat_cua_nguoi_khac(): void
    {
        $this->seed();

        $sent = $this->postJson(route('support.message.send'), [
            'name' => 'Phụ huynh A',
            'phone' => '0912345678',
            'message' => 'Tư vấn gói cho con',
        ])->assertOk()->json('message_id');

        // Người khác (phiên mới) biết ID và số điện thoại vẫn không đọc được hội thoại
        $this->flushSession();
        $this->getJson(route('support.message.check', ['id' => $sent]))
            ->assertOk()
            ->assertJson(['ok' => false])
            ->assertJsonMissing(['contact' => '0912345678']);

        $this->flushSession();
        $response = $this->postJson(route('support.message.send'), [
            'name' => 'Kẻ lạ',
            'phone' => '0912345678',
            'message' => 'Chen vào hội thoại',
        ])->assertOk();

        // Hội thoại của phụ huynh A không bị nối thêm tin nhắn của người lạ
        $this->assertNotSame($sent, $response->json('message_id'));
        $this->assertCount(1, SupportMessage::findOrFail($sent)->conversation_history ?? []);
    }

    public function test_hoc_sinh_lam_lai_bai_khong_cay_duoc_sao(): void
    {
        $this->seed();
        $student = $this->student();
        $student->reward_stars = 0;
        $student->save();

        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $this->practiceQuestion($test);
        $payload = ['duration_seconds' => 30, 'answers' => [0 => [0]]];

        $first = $this->actingAs($student)->postJson(route('attempts.store', $test), $payload)->assertOk();
        $this->assertSame(1000, $first->json('earned_stars'));

        $second = $this->actingAs($student)->postJson(route('attempts.store', $test), $payload)->assertOk();
        $this->assertSame(0, $second->json('earned_stars'));

        $student->refresh();
        $this->assertSame(1000, (int) $student->reward_stars);
    }

    public function test_nop_bai_rong_khong_lo_dap_an(): void
    {
        $this->seed();
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $this->practiceQuestion($test);

        $response = $this->actingAs($this->student())
            ->postJson(route('attempts.store', $test), ['duration_seconds' => 1, 'answers' => []])
            ->assertOk();

        $this->assertSame([], $response->json('correct_answers_data'));
    }

    public function test_so_tay_cau_sai_chi_cham_cau_trong_so_va_chi_thuong_sao_mot_lan(): void
    {
        $this->seed();
        $student = $this->student();
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $ownQuestion = $this->practiceQuestion($test);
        $foreignQuestion = $this->practiceQuestion($test);

        StudentMistake::create([
            'user_id' => $student->id,
            'question_id' => $ownQuestion->id,
            'practice_test_id' => $test->id,
            'wrong_count' => 1,
            'correct_count' => 0,
            'last_student_answer' => 1,
            'status' => 'unresolved',
            'last_wrong_at' => now(),
        ]);

        $payload = [
            'question_ids' => [$ownQuestion->id, $foreignQuestion->id],
            'answers' => [0 => [0], 1 => [0]],
        ];

        $first = $this->actingAs($student)->postJson(route('mistakes.submit'), $payload)->assertOk();
        $this->assertSame(1, $first->json('total_questions'));
        $this->assertSame(1, $first->json('resolved_count'));
        $this->assertSame(50, $first->json('earned_stars'));

        // Làm lại câu đã khắc phục không được cộng sao thêm
        $second = $this->actingAs($student)->postJson(route('mistakes.submit'), $payload)->assertOk();
        $this->assertSame(0, $second->json('earned_stars'));
    }

    public function test_telegram_khong_ghi_duoc_ky_tu_xuong_dong_vao_env(): void
    {
        $this->seed();

        $this->actingAs($this->admin())->postJson(route('admin.telegram.save'), [
            'bot_token' => "123456:abcdefghijklmnopqrstuvwxyz\nAPP_DEBUG=true",
        ])->assertStatus(422);

        $this->actingAs($this->admin())->postJson(route('admin.telegram.save'), [
            'bot_token' => '123456:abcdefghijklmnopqrstuvwxyz',
            'admin_chat_id' => "1\nAPP_KEY=x",
        ])->assertStatus(422);
    }

    public function test_doi_email_phai_nhap_dung_mat_khau_hien_tai(): void
    {
        $this->seed();
        $student = $this->student();

        $this->actingAs($student)->postJson(route('profile.update-email'), [
            'email' => 'moi@example.com',
        ])->assertStatus(422);

        $this->actingAs($student)->postJson(route('profile.update-email'), [
            'email' => 'moi@example.com',
            'current_password' => 'sai-mat-khau',
        ])->assertStatus(422);

        $this->actingAs($student)->postJson(route('profile.update-email'), [
            'email' => 'moi@example.com',
            'current_password' => '123456',
        ])->assertOk();

        $this->assertSame('moi@example.com', $student->fresh()->email);
    }

    public function test_khach_khong_xem_duoc_don_khong_do_minh_tao(): void
    {
        $this->seed();
        $order = $this->pendingOrderFor($this->teacher());

        $this->flushSession();
        $this->get(route('pricing.order.checkout', $order))->assertNotFound();

        // Chính trình duyệt tạo đơn thì xem được
        $this->flushSession();
        $this->withSession(['owned_order_ids' => [$order->id]])
            ->get(route('pricing.order.checkout', $order))
            ->assertOk();
    }

    public function test_tru_gio_choi_bi_gioi_han_boi_thoi_gian_thuc(): void
    {
        $this->seed();
        $student = $this->student();
        $student->game_time_seconds = 600;
        $student->save();

        // Không có phiên chơi đang mở thì không trừ được giây nào
        $this->actingAs($student)->postJson(route('games.consume'), ['seconds' => 600])
            ->assertOk()
            ->assertJson(['accepted_seconds' => 0, 'remaining_seconds' => 600]);

        // Mở phiên chơi rồi gửi ngay 600 giây: chỉ được trừ tối đa ~15 giây (độ trễ cho phép)
        $this->actingAs($student)->getJson(route('games.time'))->assertOk();
        $response = $this->actingAs($student)->postJson(route('games.consume'), ['seconds' => 600])->assertOk();

        $this->assertLessThanOrEqual(15, $response->json('accepted_seconds'));
        $this->assertGreaterThanOrEqual(585, $response->json('remaining_seconds'));
    }

    /** Các trang chính của từng vai trò phải mở được, không có 404/500 */
    public function test_cac_trang_chinh_mo_duoc_cho_tung_vai_tro(): void
    {
        $this->seed();

        foreach (['/', '/hoc-tap', '/thanh-tich', '/tro-choi', '/so-tay-cau-sai', '/bang-gia', '/lich-su-thue-goi'] as $url) {
            $response = $this->actingAs($this->student())->get($url);
            $this->assertSame(200, $response->status(), $url);
        }

        foreach (['/quan-tri', '/quan-tri/tong-quan', '/quan-tri/quan-ly'] as $url) {
            $response = $this->actingAs($this->teacher())->get($url);
            $this->assertSame($url === '/quan-tri/quan-ly' ? 302 : 200, $response->status(), $url);
        }

        foreach (['/quan-tri', '/quan-tri/bo-de-cau-hoi', '/quan-tri/goi-dich-vu', '/quan-tri/tro-choi/cai-dat', '/quan-tri/bo-de-thi-thu'] as $url) {
            $response = $this->actingAs($this->admin())->get($url);
            // Gói dịch vụ có route chuyển hướng sang tab trên dashboard (đúng thiết kế cũ)
            $this->assertSame($url === '/quan-tri/goi-dich-vu' ? 302 : 200, $response->status(), $url);
        }
    }
}
