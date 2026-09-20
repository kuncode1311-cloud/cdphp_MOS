<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\GameSettingController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PracticeTestController;
use App\Http\Controllers\Admin\QuestionAssetController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ thống Luyện thi IC3 GS6 & Quản trị viên
|--------------------------------------------------------------------------
| Được tổ chức chuẩn RESTful MVC và PSR-12, phân tách rõ ràng giữa:
| 1. Khách vãng lai (Guest Auth)
| 2. Học sinh làm bài (Learning App)
| 3. Quản trị viên & Studio Soạn câu hỏi (Admin & Studio)
*/

// --- 1. XÁC THỰC NGƯỜI DÙNG (AUTHENTICATION) ---
// Đọc luồng từ đây: URL → controller → model/service lấy dữ liệu → view hiển thị.
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/dang-xuat', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// --- 2. CỔNG HỌC SINH & LUYỆN TẬP (LEARNING PORTAL) ---
Route::middleware('auth')->group(function () {
    Route::get('/', [LearningController::class, 'home'])->name('home');
    Route::get('/hoc-tap', [LearningController::class, 'programs'])->name('programs');
    Route::get('/thanh-tich', [LearningController::class, 'achievements'])->name('achievements');
    Route::get('/tro-choi', [LearningController::class, 'games'])->name('games');
    Route::post('/tro-choi/doi-goi', [LearningController::class, 'exchangeGamePackage'])->name('games.exchange');
    Route::get('/tro-choi/thoi-gian', [LearningController::class, 'getGameTime'])->name('games.time');
    Route::post('/tro-choi/tieu-hao-thoi-gian', [LearningController::class, 'consumeGameTime'])->name('games.consume');
    Route::get('/phu-huynh', [LearningController::class, 'parentDashboard'])
        ->middleware('student')
        ->name('parent.dashboard');
    // {level:slug}: Laravel tìm model Level bằng slug trên URL rồi truyền vào controller.
    Route::get('/chuong-trinh/{level:slug}', [LearningController::class, 'level'])->name('levels.show');
    Route::get('/bai-luyen/{practiceTest:slug}', [LearningController::class, 'test'])->name('tests.show');
    Route::get('/bai-luyen/{practiceTest:slug}/lam-bai', [LearningController::class, 'launch'])->name('tests.launch');
    Route::post('/bai-luyen/{practiceTest:slug}/ket-qua', [AttemptController::class, 'store'])->name('attempts.store');

    // 🛡️ BỘ LỌC ĐỊNH TUYẾN THÔNG MINH: Tự động sửa lỗi & chuyển hướng nếu URL bị gõ khoảng trắng (%20), dấu gạch dưới (_)
    Route::get('/{prefix}/{slug}/{action?}', function (string $prefix, string $slug, ?string $action = null) {
        $cleanPrefix = str_replace(['_', ' ', '%20'], '-', strtolower(urldecode(trim($prefix))));
        if (! in_array($cleanPrefix, ['bai-luyen', 'bailuyen'], true)) {
            abort(404);
        }

        $cleanSlug = str_replace(['_', ' ', '%20', '.'], '-', strtolower(urldecode(trim($slug))));
        $cleanAction = $action ? str_replace(['_', ' ', '%20'], '-', strtolower(urldecode(trim($action)))) : null;

        if ($cleanAction === 'lam-bai' || $cleanAction === 'lambai') {
            return redirect()->route('tests.launch', ['practiceTest' => $cleanSlug]);
        }

        return redirect()->route('tests.show', ['practiceTest' => $cleanSlug]);
    })->where('prefix', '[^/]*bai[^/]*luyen[^/]*');

    Route::get('/{prefix}/{slug}', function (string $prefix, string $slug) {
        $cleanPrefix = str_replace(['_', ' ', '%20'], '-', strtolower(urldecode(trim($prefix))));
        if ($cleanPrefix !== 'chuong-trinh' && $cleanPrefix !== 'chuongtrinh') {
            abort(404);
        }
        $cleanSlug = str_replace(['_', ' ', '%20', '.'], '-', strtolower(urldecode(trim($slug))));
        return redirect()->route('levels.show', ['level' => $cleanSlug]);
    })->where('prefix', '[^/]*chuong[^/]*trinh[^/]*');

    // Mua & Đăng ký gói bản quyền
    Route::post('/bang-gia/thue-goi/{package:slug}', [PricingController::class, 'order'])->name('pricing.order');
    Route::post('/bang-gia/thanh-toan-payos/{order:code}', [PricingController::class, 'createPayosLink'])->name('pricing.order.payos');
    Route::get('/lich-su-thue-goi', [PricingController::class, 'history'])->name('pricing.history');
});

// Bảng giá xem công khai cho tất cả người dùng
Route::redirect('/bang_gia', '/bang-gia');
Route::get('/bang-gia', [PricingController::class, 'index'])->name('pricing.index');
Route::post('/bang-gia/dang-ky-va-thue-goi/{package:slug}', [PricingController::class, 'registerAndOrder'])->name('pricing.register_and_order');
Route::get('/bang-gia/thanh-toan/{order:code}', [PricingController::class, 'checkout'])->name('pricing.order.checkout');
Route::get('/bang-gia/don-hang/{order:code}/trang-thai', [PricingController::class, 'checkOrderStatus'])->name('pricing.order.status');
Route::post('/bang-gia/don-hang/{order:code}/da-chuyen-khoan', [PricingController::class, 'confirmTransferred'])->name('pricing.order.confirm_transferred');
Route::get('/bang-gia/payos-tra-ve/{order:code}', [PricingController::class, 'payosReturn'])->name('pricing.payos.return');
Route::post('/bang-gia/payos-webhook', [PricingController::class, 'payosWebhook'])->name('pricing.payos.webhook');

// Live Chat Messenger gửi tin nhắn tư vấn trực tiếp cho Admin (Telegram)
Route::post('/ho-tro/gui-tin-nhan', [PricingController::class, 'sendSupportMessage'])->name('support.message.send');
Route::get('/ho-tro/tin-nhan/kiem-tra', [PricingController::class, 'checkSupportMessageReply'])->name('support.message.check');

// Telegram Webhook nhận lệnh từ bot riêng (@sp_trikun_bot)
Route::post('/api/telegram/webhook', [TelegramBotController::class, 'handleWebhook'])->name('telegram.webhook');

// --- 3. TRUNG TÂM ĐIỀU HÀNH & STUDIO SOẠN ĐỀ IC3 (ADMIN PORTAL) ---
// Cho admin và giáo viên vào; từng chức năng còn kiểm tra quyền riêng.
Route::prefix('quan-tri')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Bảng điều khiển tổng quan & Báo cáo thống kê
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tong-quan', [AdminController::class, 'dashboard'])->name('overview');
    Route::get('/xuat-bao-cao', [AdminController::class, 'exportCsv'])->name('attempts.export');
    Route::delete('/attempts/{attempt}', [AdminController::class, 'destroyAttempt'])->name('attempts.destroy');

    // Cài đặt cấu hình Khu trò chơi, Tỷ lệ đổi Sao & Bảng xếp hạng thi đua
    Route::get('/tro-choi/cai-dat', [GameSettingController::class, 'index'])->name('games.settings');
    Route::match(['post', 'put'], '/tro-choi/cai-dat', [GameSettingController::class, 'update'])->name('games.settings.update');
    Route::post('/tro-choi/dieu-chinh-sao', [GameSettingController::class, 'adjustStars'])->name('games.adjust-stars');
    Route::post('/tro-choi/bang-xep-hang/cai-dat', [GameSettingController::class, 'updateLeaderboardSettings'])->name('games.leaderboard.settings');
    Route::post('/tro-choi/bang-xep-hang/reset', [GameSettingController::class, 'resetLeaderboard'])->name('games.leaderboard.reset');

    // IC3 QUESTION STUDIO - Quản trị Bộ đề & Soạn thảo câu hỏi trọn gói
    Route::get('/bo-de-cau-hoi', [QuestionController::class, 'index'])->name('questions.studio');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Quản lý Bộ đề (PracticeTest)
    Route::post('/tests', [PracticeTestController::class, 'store'])->name('tests.store');
    Route::put('/tests/{practiceTest}', [PracticeTestController::class, 'update'])->name('tests.update');
    Route::delete('/tests/{practiceTest}', [PracticeTestController::class, 'destroy'])->name('tests.destroy');

    // Quản lý Chủ đề (Topic)
    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');

    // Quản lý Tệp tin tài nguyên câu hỏi
    Route::delete('/assets/{asset}', [QuestionAssetController::class, 'destroy'])->name('assets.destroy');

    // Quản lý Người dùng, Lớp học và Cấu trúc tổng thể
    Route::get('/quan-ly', [AdminManagementController::class, 'index'])->name('management');
    Route::post('/lop', [ClassroomController::class, 'store'])->name('classes.store');
    Route::post('/hoc-sinh', [UserController::class, 'store'])->name('students.store');
    Route::resource('users', UserController::class)->only(['store', 'update', 'destroy']);
    Route::resource('classrooms', ClassroomController::class)->only(['update', 'destroy']);
    Route::post('/programs', [AdminManagementController::class, 'storeProgram'])->name('programs.store');
    Route::put('/programs/{program}', [AdminManagementController::class, 'updateProgram'])->name('programs.update');
    Route::delete('/programs/{program}', [AdminManagementController::class, 'destroyProgram'])->name('programs.destroy');
    Route::post('/levels', [AdminManagementController::class, 'storeLevel'])->name('levels.store');
    Route::put('/levels/{level}', [AdminManagementController::class, 'updateLevel'])->name('levels.update');
    Route::delete('/levels/{level}', [AdminManagementController::class, 'destroyLevel'])->name('levels.destroy');

    // Quản lý Gói dịch vụ & Đơn thuê bản quyền phần mềm (Admin Packages & Orders)
    Route::get('/goi-dich-vu', [AdminPackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [AdminPackageController::class, 'store'])->name('packages.store');
    Route::put('/packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');
    Route::post('/packages/{package}/toggle', [AdminPackageController::class, 'toggle'])->name('packages.toggle');
    Route::post('/orders/{order}/activate', [AdminPackageController::class, 'activateOrder'])->name('orders.activate');
    Route::post('/orders/{order}/reject', [AdminPackageController::class, 'rejectOrder'])->name('orders.reject');

    // Quản lý tin nhắn tư vấn Live Chat từ website & Telegram Bot
    Route::get('/tin-nhan/realtime-poll', [AdminController::class, 'pollSupportMessages'])->name('support.poll');
    Route::patch('/tin-nhan/{supportMessage}/trang-thai', [AdminController::class, 'updateSupportMessageStatus'])->name('support.status');
    Route::delete('/tin-nhan/{supportMessage}', [AdminController::class, 'deleteSupportMessage'])->name('support.delete');
    Route::post('/cai-dat-telegram', [AdminController::class, 'saveTelegramConfig'])->name('telegram.save');
    Route::post('/gui-thu-telegram', [AdminController::class, 'testTelegramNotification'])->name('telegram.test');
    Route::post('/telegram/lay-chat-id', [AdminController::class, 'getTelegramChatId'])->name('telegram.get_chat_id');
    Route::post('/telegram/phan-hoi', [AdminController::class, 'sendAdminReplyViaTelegram'])->name('telegram.reply');
});
