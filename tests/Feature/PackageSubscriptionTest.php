<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Level;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Khách và Người dùng có thể xem trang Bảng giá công khai
     */
    public function test_pricing_page_is_accessible_and_displays_active_packages(): void
    {
        $this->seed();

        $response = $this->get(route('pricing.index'));

        $response->assertOk();
        $response->assertSee('Gói Khởi Đầu');
        $response->assertSee('Gói Tiêu Chuẩn');
        $response->assertSee('Gói Trường Học Toàn Diện');
    }

    /**
     * Test: Giáo viên có thể tạo đơn đăng ký thuê gói
     */
    public function test_teacher_can_create_package_order(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();

        $response = $this->actingAs($teacher)->post(route('pricing.order', $package), [
            'payment_method' => 'bank_transfer',
            'notes' => 'Cô Mai Linh thuê gói học kỳ 1',
        ]);

        $order = PackageOrder::where('user_id', $teacher->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($package->id, $order->package_id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals($package->price, $order->price);

        $response->assertRedirect(route('pricing.order.checkout', $order));
    }

    /**
     * Test: Khách chưa có tài khoản có thể đăng ký tài khoản Giáo viên mới VÀ tạo đơn thuê gói ngay lập tức
     */
    public function test_guest_can_register_new_teacher_account_and_create_order(): void
    {
        $this->seed();

        $package = Package::where('slug', 'goi-khoi-dau-starter')->firstOrFail();

        $response = $this->post(route('pricing.register_and_order', $package), [
            'name' => 'Thầy Hoàng Nam',
            'email' => 'hoangnam.gv@ic3.test',
            'password' => 'matkhau123',
            'phone' => '0988776655',
            'school_name' => 'TH Lê Quý Đôn',
            'payment_method' => 'bank_transfer',
        ]);

        // Khẳng định tài khoản Giáo viên mới đã được tạo với trạng thái pending (chờ kích hoạt)
        $teacher = User::where('email', 'hoangnam.gv@ic3.test')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals('Thầy Hoàng Nam', $teacher->name);
        $this->assertEquals(UserRole::Teacher->value, $teacher->role);
        $this->assertEquals('pending', $teacher->status);

        // Khẳng định chưa đăng nhập ngay (chờ thanh toán hoặc admin duyệt mới kích hoạt)
        $this->assertGuest();

        // Khẳng định đơn hàng đã được tạo
        $order = PackageOrder::where('user_id', $teacher->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($package->id, $order->package_id);
        $this->assertStringContainsString('TH Lê Quý Đôn', $order->notes);

        $response->assertRedirect(route('pricing.order.checkout', $order));
    }

    /**
     * Test: Học sinh không thể thuê gói giáo viên
     */
    public function test_student_cannot_order_teacher_package(): void
    {
        $this->seed();

        $student = User::where('student_code', 'HS001')->firstOrFail();
        $package = Package::firstOrFail();

        $response = $this->actingAs($student)->post(route('pricing.order', $package), [
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHas('err');
        $this->assertDatabaseMissing('package_orders', [
            'user_id' => $student->id,
        ]);
    }

    /**
     * Test: Giáo viên có thể xem trang checkout với mã VietQR và thông tin thanh toán
     */
    public function test_teacher_can_view_checkout_page(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::firstOrFail();

        $order = PackageOrder::create([
            'code' => 'MOS-TEST-12345',
            'user_id' => $teacher->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => $package->price,
            'duration_days' => $package->duration_days,
            'max_students' => $package->max_students,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($teacher)->get(route('pricing.order.checkout', $order));

        $response->assertOk();
        $response->assertSee('MOS-TEST-12345');
        $response->assertSee('VietQR');
    }

    /**
     * Test: Admin có thể quản lý CRUD gói dịch vụ
     */
    public function test_admin_can_crud_packages(): void
    {
        $this->seed();

        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();
        $level3 = Level::where('grade', 3)->firstOrFail();

        // 1. Thêm gói mới
        $responseStore = $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Gói Thử Nghiệm VIP',
            'price' => 500000,
            'duration_days' => 45,
            'max_students' => 60,
            'level_ids' => [$level3->id],
            'features_text' => "Tính năng 1\nTính năng 2",
            'is_active' => 1,
        ]);

        $responseStore->assertRedirect();
        $newPkg = Package::where('name', 'Gói Thử Nghiệm VIP')->first();
        $this->assertNotNull($newPkg);
        $this->assertEquals(45, $newPkg->duration_days);
        $this->assertTrue($newPkg->levels->contains('id', $level3->id));

        // 2. Sửa gói
        $responseUpdate = $this->actingAs($admin)->put(route('admin.packages.update', $newPkg), [
            'name' => 'Gói Thử Nghiệm VIP (Đã Sửa)',
            'price' => 550000,
            'duration_days' => 60,
            'max_students' => 80,
            'level_ids' => [$level3->id],
            'is_active' => 1,
        ]);

        $responseUpdate->assertRedirect();
        $this->assertEquals('Gói Thử Nghiệm VIP (Đã Sửa)', $newPkg->fresh()->name);
        $this->assertEquals(80, $newPkg->fresh()->max_students);

        // 3. Ẩn / Hiện gói (Toggle)
        $this->actingAs($admin)->post(route('admin.packages.toggle', $newPkg));
        $this->assertFalse($newPkg->fresh()->is_active);

        // 4. Xóa gói
        $this->actingAs($admin)->delete(route('admin.packages.destroy', $newPkg));
        $this->assertDatabaseMissing('packages', ['id' => $newPkg->id]);
    }

    /**
     * Test: Admin duyệt kích hoạt đơn hàng -> Tự động cập nhật tài khoản Giáo viên
     */
    public function test_admin_can_activate_order_and_teacher_is_updated(): void
    {
        $this->seed();

        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        
        // Giả sử giáo viên hiện có hạn mức 100 học sinh, hết hạn vào ngày mai
        $initialExpires = Carbon::now()->addDay()->startOfDay();
        $teacher->update([
            'max_students' => 100,
            'expires_at' => $initialExpires,
            'status' => 'active',
        ]);

        $level4 = Level::where('grade', 4)->firstOrFail();
        $level5 = Level::where('grade', 5)->firstOrFail();

        // Gói Trường Học Toàn Diện: 365 ngày, 300 học sinh, Khối 3-4-5
        $pkgPro = Package::where('slug', 'goi-truong-hoc-toan-dien')->firstOrFail();

        $order = PackageOrder::create([
            'code' => 'MOS-ORD-ACTIVATE',
            'user_id' => $teacher->id,
            'package_id' => $pkgPro->id,
            'package_name' => $pkgPro->name,
            'price' => $pkgPro->price,
            'duration_days' => $pkgPro->duration_days, // 365
            'max_students' => $pkgPro->max_students,   // 300
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        // Admin bấm kích hoạt đơn
        $response = $this->actingAs($admin)->post(route('admin.orders.activate', $order));

        $response->assertRedirect();
        $this->assertEquals('active', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->activated_at);

        // Kiểm tra giáo viên được cập nhật
        $teacher->refresh();
        $this->assertEquals(300, $teacher->max_students); // Nâng quota từ 100 lên 300
        
        // Thời hạn được cộng dồn thêm 365 ngày từ ngày hết hạn cũ
        $expectedExpiry = $initialExpires->copy()->addDays(365)->format('Y-m-d');
        $this->assertEquals($expectedExpiry, $teacher->expires_at->format('Y-m-d'));

        // Kiểm tra khối lớp được gán thêm
        $this->assertTrue($teacher->canAccessLevel($level4));
        $this->assertTrue($teacher->canAccessLevel($level5));
    }

    /**
     * Test: Bảng điều khiển Admin tích hợp tab Quản trị Gói và KHÔNG hiển thị nút "Nâng Cấp Gói" trên topbar Admin
     */
    public function test_admin_dashboard_includes_packages_tab_and_hides_upgrade_button_for_admin(): void
    {
        $this->seed();

        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('tab-packages');
        $response->assertSee('Gói Dịch Vụ');
        $response->assertSee('Gói Tiêu Chuẩn');
        
        // Admin không có nút Nâng Cấp Gói trên navbar
        $response->assertDontSee('✨</span> Nâng Cấp Gói', false);
    }

    /**
     * Test: Giáo viên có nút "Nâng Cấp Gói" trên topbar và trong thẻ trạng thái giảng dạy
     */
    public function test_teacher_dashboard_shows_upgrade_button(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();

        $response = $this->actingAs($teacher)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('✨</span> Nâng Cấp Gói', false);
    }

    /**
     * Test: Truy cập route admin.packages.index tự động chuyển hướng về admin.dashboard#tab-packages
     */
    /**
     * Test: Người dùng có thể gửi tin nhắn hỗ trợ từ widget Live Chat và lưu vào CSDL
     */
    public function test_user_can_submit_live_chat_support_message(): void
    {
        $response = $this->postJson(route('support.message.send'), [
            'name' => 'Cô Thu Hà',
            'contact' => '0912345999',
            'message' => 'Em muốn đăng ký gói Tiêu Chuẩn cho 2 lớp Khối 3 và 4 ạ!',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('support_messages', [
            'name' => 'Cô Thu Hà',
            'phone' => '0912345999',
            'message' => 'Em muốn đăng ký gói Tiêu Chuẩn cho 2 lớp Khối 3 và 4 ạ!',
        ]);

        $messageId = $response->json('message_id');

        // Nhắn câu thứ 2 kèm parent_id -> Phải gộp vào cùng cuộc trò chuyện
        $response2 = $this->postJson(route('support.message.send'), [
            'name' => 'Cô Thu Hà',
            'contact' => '0912345999',
            'message' => 'Trường mình có 80 em học sinh nhé.',
            'parent_id' => $messageId,
        ]);

        $response2->assertOk();
        $this->assertEquals($messageId, $response2->json('message_id'));

        $firstMsg = \App\Models\SupportMessage::find($messageId);
        $this->assertStringContainsString('Em muốn đăng ký gói Tiêu Chuẩn', $firstMsg->message);
        $this->assertStringContainsString('Trường mình có 80 em học sinh nhé.', $firstMsg->message);
        $this->assertCount(2, $firstMsg->conversation_history);
    }

    /**
     * Test: Đảm bảo luồng chat liền mạch nhiều lượt (multi-turn), bảo toàn lịch sử và thứ tự tin nhắn
     */
    public function test_multi_turn_live_chat_conversation_history_preservation_and_ordering(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@ic3.test')->firstOrFail();

        // 1. Khách gửi tin nhắn thứ 1: "mua gói"
        $res1 = $this->postJson(route('support.message.send'), [
            'name' => 'trí đẹp zai',
            'contact' => '0345151438',
            'phone' => '0345151438',
            'message' => 'mua gói',
        ]);
        $res1->assertOk();
        $msgId = $res1->json('message_id');

        // 2. Khách gửi tiếp tin nhắn thứ 2: "dạ"
        $res2 = $this->postJson(route('support.message.send'), [
            'name' => 'trí đẹp zai',
            'contact' => '0345151438',
            'phone' => '0345151438',
            'message' => 'dạ',
            'parent_id' => $msgId,
        ]);
        $res2->assertOk();
        $this->assertEquals($msgId, $res2->json('message_id'));

        // 3. Admin phản hồi lượt 1: "ok ạ"
        $resAdmin1 = $this->actingAs($admin)->patchJson(route('admin.support.status', $msgId), [
            'status' => 'resolved',
            'admin_reply' => 'ok ạ',
        ]);
        $resAdmin1->assertOk();

        // 4. Admin phản hồi tiếp lượt 2: "❤️" (không làm mất "ok ạ")
        $resAdmin2 = $this->actingAs($admin)->patchJson(route('admin.support.status', $msgId), [
            'status' => 'resolved',
            'admin_reply' => '❤️',
        ]);
        $resAdmin2->assertOk();

        // 5. Kiểm tra lịch sử trò chuyện qua API kiểm tra tin nhắn
        $checkRes = $this->getJson(route('support.message.check', ['id' => $msgId]));
        $checkRes->assertOk();
        $history = $checkRes->json('conversation_history');

        $this->assertIsArray($history);
        $this->assertCount(4, $history);

        // Kiểm tra đúng thứ tự thời gian và người gửi
        $this->assertEquals('user', $history[0]['sender']);
        $this->assertEquals('mua gói', $history[0]['text']);

        $this->assertEquals('user', $history[1]['sender']);
        $this->assertEquals('dạ', $history[1]['text']);

        $this->assertEquals('admin', $history[2]['sender']);
        $this->assertEquals('ok ạ', $history[2]['text']);

        $this->assertEquals('admin', $history[3]['sender']);
        $this->assertEquals('❤️', $history[3]['text']);
    }

    /**
     * Test: Đặt thuê gói với phương thức thanh toán PayOS
     */
    public function test_teacher_can_order_package_with_payos_method(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();

        $response = $this->actingAs($teacher)->post(route('pricing.order', $package), [
            'payment_method' => 'payos',
            'notes' => 'Thanh toán trực tuyến PayOS',
        ]);

        $order = PackageOrder::where('user_id', $teacher->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('payos', $order->payment_method);
        // Do sandbox / credentials PayOS hợp lệ có thể redirect hoặc checkout
        $this->assertTrue($response->isRedirect());
    }

    /**
     * Test: Trang checkout hiển thị tùy chọn chuyển sang cổng PayOS
     */
    public function test_checkout_page_displays_payos_instant_payment_banner(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-khoi-dau-starter')->firstOrFail();

        $order = PackageOrder::create([
            'code' => 'ORD-TEST123',
            'user_id' => $teacher->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => $package->price,
            'duration_days' => $package->duration_days,
            'max_students' => $package->max_students,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($teacher)->get(route('pricing.order.checkout', $order));

        $response->assertOk();
        $response->assertSee('ORD-TEST123');
        $response->assertSee('Thanh Toán Chuyển Khoản');
    }

    /**
     * Test: Telegram Bot Webhook tiếp nhận và phản hồi các lệnh (/trogiup, /doanhthu, /choduyet, /danhsachchat)
     */
    public function test_telegram_bot_can_process_webhook_commands(): void
    {
        $this->seed();

        // 1. Lệnh /trogiup
        $response = $this->postJson(route('telegram.webhook'), [
            'message' => [
                'chat' => ['id' => '8732001731'],
                'text' => '/trogiup',
                'from' => ['first_name' => 'Admin'],
            ],
        ]);
        $response->assertOk();
        $response->assertJson(['status' => 'ok']);

        // 2. Lệnh /choduyet
        $response = $this->postJson(route('telegram.webhook'), [
            'message' => [
                'chat' => ['id' => '8732001731'],
                'text' => '/choduyet',
                'from' => ['first_name' => 'Admin'],
            ],
        ]);
        $response->assertOk();
        $response->assertJson(['status' => 'ok']);
    }

    /**
     * Test: Khách có thể đăng ký tài khoản Học sinh mới khi chọn mua gói học sinh
     */
    public function test_guest_can_register_new_student_account_and_create_order(): void
    {
        $this->seed();

        $level3 = Level::where('grade', 3)->first();
        $studentPackage = Package::create([
            'slug' => 'goi-hoc-sinh-test',
            'name' => 'Gói Tự Luyện Khối 3',
            'target_audience' => 'student',
            'price' => 69000,
            'duration_days' => 30,
            'max_students' => 1,
            'is_active' => true,
        ]);
        if ($level3) {
            $studentPackage->levels()->sync([$level3->id]);
        }

        $response = $this->post(route('pricing.register_and_order', $studentPackage), [
            'name' => 'Bé Nguyễn An Nhiên',
            'email' => 'annhien.hocsinh@ic3.test',
            'password' => 'matkhau123',
            'phone' => '0912345678',
            'school_name' => 'Lớp 3A1',
            'payment_method' => 'bank_transfer',
        ]);

        $student = User::where('email', 'annhien.hocsinh@ic3.test')->first();
        $this->assertNotNull($student);
        $this->assertEquals(UserRole::Student->value, $student->role);
        $this->assertEquals(1, $student->max_students);
        $this->assertEquals('pending', $student->status);

        $order = PackageOrder::where('user_id', $student->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($studentPackage->id, $order->package_id);
        $this->assertTrue($order->isStudentOrder());

        $response->assertRedirect(route('pricing.order.checkout', $order));
    }

    /**
     * Test: Kích hoạt đơn hàng của Học sinh sẽ cấp quyền vào accessibleLevels và gia hạn expires_at
     */
    public function test_activating_student_order_grants_accessible_levels_and_expiry(): void
    {
        $this->seed();

        $level3 = Level::where('grade', 3)->firstOrFail();
        $level4 = Level::where('grade', 4)->firstOrFail();

        $studentPackage = Package::create([
            'slug' => 'goi-but-pha-test',
            'name' => 'Gói Bứt Phá Khối 3-4',
            'target_audience' => 'student',
            'price' => 149000,
            'duration_days' => 90,
            'max_students' => 1,
            'is_active' => true,
        ]);
        $studentPackage->levels()->sync([$level3->id, $level4->id]);

        $student = User::create([
            'name' => 'Bé Minh Đăng',
            'email' => 'minhdang@ic3.test',
            'password' => '123456',
            'role' => UserRole::Student->value,
            'status' => 'pending',
        ]);

        $order = PackageOrder::create([
            'code' => 'ORD-STUDENT-TEST',
            'user_id' => $student->id,
            'package_id' => $studentPackage->id,
            'package_name' => $studentPackage->name,
            'price' => $studentPackage->price,
            'duration_days' => $studentPackage->duration_days,
            'max_students' => 1,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $service = app(\App\Services\SubscriptionService::class);
        $result = $service->activateOrder($order);

        $this->assertTrue($result);

        $student->refresh();
        $this->assertEquals('active', $student->status);
        $this->assertNotNull($student->expires_at);
        $this->assertTrue($student->expires_at->isFuture());

        // Kiểm tra quyền truy cập qua level_user (accessibleLevels)
        $this->assertTrue($student->canAccessLevel($level3));
        $this->assertTrue($student->canAccessLevel($level4));
    }

    /**
     * Test: Học sinh đã đăng nhập có thể tạo đơn mua gói học sinh
     */
    public function test_student_can_order_student_package_when_logged_in(): void
    {
        $this->seed();

        $student = User::where('role', UserRole::Student->value)->firstOrFail();
        $studentPackage = Package::create([
            'slug' => 'goi-luyen-nhanh-test',
            'name' => 'Gói Luyện Nhanh',
            'target_audience' => 'student',
            'price' => 49000,
            'duration_days' => 15,
            'max_students' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)->post(route('pricing.order', $studentPackage), [
            'payment_method' => 'bank_transfer',
            'phone' => '0933221100',
        ]);

        $order = PackageOrder::where('user_id', $student->id)->where('package_id', $studentPackage->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('pricing.order.checkout', $order));
    }

    /**
     * Test: Học sinh kế thừa hạn dùng và trạng thái từ gói của Giáo viên phụ trách
     */
    public function test_student_inherits_teacher_subscription_status_and_level_access(): void
    {
        $this->seed();

        $teacher = User::where('role', UserRole::Teacher->value)->firstOrFail();
        $teacher->update([
            'status' => 'active',
            'expires_at' => now()->addDays(30),
        ]);

        $level = Level::firstOrFail();
        $teacher->teacherLevels()->sync([$level->id]);

        // Tạo học sinh thuộc giáo viên này
        $student = User::create([
            'name' => 'Học Sinh Thử Nghiệm',
            'email' => 'hs_inherit_test@mos.test',
            'password' => bcrypt('12345678'),
            'role' => UserRole::Student->value,
            'created_by' => $teacher->id,
            'status' => 'active',
            'expires_at' => null, // Không có hạn riêng, kế thừa từ giáo viên
        ]);
        $student->accessibleLevels()->sync([$level->id]);

        // 1. Khi giáo viên còn hạn -> học sinh được phép học
        $this->assertTrue($student->isSubscriptionActive());
        $this->assertTrue($student->canAccessLevel($level));

        // 2. Khi giáo viên bị hết hạn -> học sinh tự động bị chặn
        $teacher->update(['expires_at' => now()->subDay()]);
        $student->unsetRelation('teacher');
        $this->assertFalse($student->isSubscriptionActive());
        $this->assertFalse($student->canAccessLevel($level));

        // 3. Khi giáo viên được gia hạn -> học sinh tự động được mở khóa lại
        $teacher->update(['expires_at' => now()->addDays(60)]);
        $student->unsetRelation('teacher');
        $this->assertTrue($student->isSubscriptionActive());
        $this->assertTrue($student->canAccessLevel($level));
    }

    /**
     * Test: Khi gán khối cho học sinh thuộc giáo viên, chỉ những khối giáo viên sở hữu mới được cấp
     */
    public function test_student_can_only_be_assigned_levels_owned_by_teacher(): void
    {
        $this->seed();

        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();
        $teacher = User::where('role', UserRole::Teacher->value)->firstOrFail();

        $allLevels = Level::all();
        $this->assertGreaterThanOrEqual(2, $allLevels->count());

        $teacherLevel = $allLevels->first();
        $unownedLevel = $allLevels->last();

        // Giáo viên chỉ sở hữu 1 khối
        $teacher->teacherLevels()->sync([$teacherLevel->id]);

        $student = User::create([
            'name' => 'Học Sinh Lớp Cô',
            'email' => 'hs_co_test@mos.test',
            'password' => bcrypt('12345678'),
            'role' => UserRole::Student->value,
            'created_by' => $teacher->id,
            'status' => 'active',
        ]);

        // Cố tình gán cả khối giáo viên có và khối giáo viên KHÔNG có
        $response = $this->actingAs($admin)
            ->putJson(route('admin.users.update', $student), [
                'name' => $student->name,
                'email' => $student->email,
                'role' => 'student',
                'created_by' => $teacher->id,
                'level_ids' => [$teacherLevel->id, $unownedLevel->id],
            ]);

        $response->assertOk();

        // Kiểm tra trong database: Chỉ khối của giáo viên mới được cấp, khối không sở hữu bị loại bỏ
        $studentLevels = $student->fresh()->accessibleLevels->pluck('id')->toArray();
        $this->assertContains($teacherLevel->id, $studentLevels);
        $this->assertNotContains($unownedLevel->id, $studentLevels);
    }
}

