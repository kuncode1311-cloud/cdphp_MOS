<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\GameSettingController;
use App\Http\Controllers\Admin\AiQuestionController;
use App\Http\Controllers\Admin\MockTestController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PracticeTestController;
use App\Http\Controllers\Admin\QuestionAssetController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SupportCallController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\MistakeController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TestController;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ thống Luyện thi IC3 GS6 & Quản trị viên
|--------------------------------------------------------------------------
| Được tổ chức chuẩn RESTful MVC và PSR-12, phân tách rõ ràng giữa:
| 1. Khách vãng lai (Guest Auth)
| 2. Học sinh làm bài (Learning App)
| 3. Quản trị viên & Studio Soạn câu hỏi (Admin & Studio)
|*/

// --- 1. XÁC THỰC NGƯỜI DÙNG (AUTHENTICATION) ---
// Đọc luồng từ đây: URL → controller → model/service lấy dữ liệu → view hiển thị.
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    // Đăng nhập bằng tài khoản Google (OAuth 2.0)
    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/dang-xuat', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// --- 2. CỔNG HỌC SINH & LUYỆN TẬP (LEARNING PORTAL) ---
Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('/', [LearningController::class, 'home'])->name('home');
    Route::redirect('/hoc_tap', '/hoc-tap');
    Route::get('/hoc-tap', [LearningController::class, 'programs'])->name('programs');
    Route::redirect('/thanh_tich', '/thanh-tich');
    Route::get('/thanh-tich', [LearningController::class, 'achievements'])->name('achievements');
    Route::redirect('/tro_choi', '/tro-choi');
    Route::get('/tro-choi', [LearningController::class, 'games'])->name('games');
    Route::post('/tro-choi/doi-goi', [LearningController::class, 'exchangeGamePackage'])->name('games.exchange');
    Route::get('/tro-choi/thoi-gian', [LearningController::class, 'getGameTime'])->name('games.time');
    Route::post('/tro-choi/tieu-hao-thoi-gian', [LearningController::class, 'consumeGameTime'])->name('games.consume');
    Route::redirect('/phu_huynh', '/phu-huynh');
    Route::get('/phu-huynh', [LearningController::class, 'parentDashboard'])
        ->middleware('student')
        ->name('parent.dashboard');
    // {level:slug}: Laravel tìm model Level bằng slug trên URL rồi truyền vào controller.
    Route::get('/chuong-trinh/{level:slug}', [LearningController::class, 'level'])->name('levels.show');
    Route::get('/bai-luyen/{practiceTest:slug}', [LearningController::class, 'test'])->name('tests.show');
    Route::get('/bai-luyen/{practiceTest:slug}/lam-bai', [LearningController::class, 'launch'])->name('tests.launch');
    Route::post('/bai-luyen/{practiceTest:slug}/ket-qua', [AttemptController::class, 'store'])->middleware('throttle:30,1')->name('attempts.store');

    // Sổ Tay Câu Sai & Phòng Luyện Tập Phục Thù (Mistake Notebook & Revenge Practice)
    Route::get('/so-tay-cau-sai', [MistakeController::class, 'index'])->name('mistakes.index');
    Route::get('/so-tay-cau-sai/lam-lai', [MistakeController::class, 'launch'])->name('mistakes.launch');
    Route::post('/so-tay-cau-sai/nop-bai', [MistakeController::class, 'submit'])->middleware('throttle:30,1')->name('mistakes.submit');

    // Hồ Sơ Cá Nhân & Đổi Mật Khẩu với xác thực OTP
    Route::post('/tai-khoan/gui-otp-mat-khau', [ProfileController::class, 'sendOtp'])->middleware('throttle:5,1')->name('profile.send-otp');
    Route::post('/tai-khoan/doi-mat-khau', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/tai-khoan/cap-nhat-email', [ProfileController::class, 'updateEmail'])->name('profile.update-email');

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
Route::post('/bang-gia/dang-ky-va-thue-goi/{package:slug}', [PricingController::class, 'registerAndOrder'])->middleware('throttle:6,1')->name('pricing.register_and_order');
Route::get('/bang-gia/thanh-toan/{order:code}', [PricingController::class, 'checkout'])->name('pricing.order.checkout');
Route::get('/bang-gia/don-hang/{order:code}/trang-thai', [PricingController::class, 'checkOrderStatus'])->middleware('throttle:60,1')->name('pricing.order.status');
Route::post('/bang-gia/don-hang/{order:code}/da-chuyen-khoan', [PricingController::class, 'confirmTransferred'])->middleware('throttle:6,1')->name('pricing.order.confirm_transferred');
Route::get('/bang-gia/payos-tra-ve/{order:code}', [PricingController::class, 'payosReturn'])->name('pricing.payos.return');
Route::post('/bang-gia/payos-webhook', [PricingController::class, 'payosWebhook'])->name('pricing.payos.webhook');

// Live Chat Messenger gửi tin nhắn tư vấn trực tiếp cho Admin (Telegram)
Route::post('/ho-tro/gui-tin-nhan', [PricingController::class, 'sendSupportMessage'])->middleware('throttle:20,1')->name('support.message.send');
Route::get('/ho-tro/tin-nhan/kiem-tra', [PricingController::class, 'checkSupportMessageReply'])->middleware('throttle:90,1')->name('support.message.check');

// Telegram Webhook nhận lệnh từ bot riêng (@sp_trikun_bot)
Route::post('/api/telegram/webhook', [TelegramBotController::class, 'handleWebhook'])->name('telegram.webhook');

// --- 3. TRUNG TÂM ĐIỀU HÀNH & STUDIO SOẠN ĐỀ IC3 (ADMIN PORTAL) ---
// Cho admin và giáo viên vào; từng chức năng còn kiểm tra quyền riêng.
Route::prefix('quan-tri')->name('admin.')->middleware(['auth', 'admin', 'subscription'])->group(function () {

    // --- Phần giáo viên cũng dùng: tổng quan lớp mình, học sinh, lớp học, xuất kết quả lớp mình ---
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tong-quan', [AdminController::class, 'dashboard'])->name('overview');
    Route::get('/xuat-bao-cao', [AdminController::class, 'exportCsv'])->name('attempts.export');
    Route::delete('/attempts/{attempt}', [AdminController::class, 'destroyAttempt'])->name('attempts.destroy');
    Route::get('/quan-ly', [AdminManagementController::class, 'index'])->name('management');
    Route::post('/lop', [ClassroomController::class, 'store'])->name('classes.store');
    Route::post('/hoc-sinh', [UserController::class, 'store'])->name('students.store');
    Route::resource('users', UserController::class)->only(['store', 'update', 'destroy']);
    Route::resource('classrooms', ClassroomController::class)->only(['update', 'destroy']);

    // --- Chỉ Quản trị viên tổng: cài đặt, đơn hàng, gói, chat hỗ trợ, Telegram, cuộc gọi, Studio, AI ---
    Route::middleware('superadmin')->group(function () {

        // Cài đặt Khu trò chơi, Tỷ lệ đổi Sao & Bảng xếp hạng thi đua
        Route::get('/tro-choi/cai-dat', [GameSettingController::class, 'index'])->name('games.settings');
        Route::match(['post', 'put'], '/tro-choi/cai-dat', [GameSettingController::class, 'update'])->name('games.settings.update');
        Route::post('/tro-choi/dieu-chinh-sao', [GameSettingController::class, 'adjustStars'])->name('games.adjust-stars');
        Route::post('/tro-choi/bang-xep-hang/cai-dat', [GameSettingController::class, 'updateLeaderboardSettings'])->name('games.leaderboard.settings');
        Route::post('/tro-choi/bang-xep-hang/reset', [GameSettingController::class, 'resetLeaderboard'])->name('games.leaderboard.reset');

        // IC3 QUESTION STUDIO
        Route::get('/bo-de-cau-hoi', [QuestionController::class, 'index'])->name('questions.studio');
        Route::get('/xem-thu/phong-thi', [LearningController::class, 'previewSimulator'])->name('preview.simulator');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        // Quản lý Bộ đề (PracticeTest)
        Route::post('/tests', [PracticeTestController::class, 'store'])->name('tests.store');
        Route::put('/tests/{practiceTest}', [PracticeTestController::class, 'update'])->name('tests.update');
        Route::delete('/tests/{practiceTest}', [PracticeTestController::class, 'destroy'])->name('tests.destroy');

        // Bộ đề thi thử tổng hợp
        Route::get('/bo-de-thi-thu', [MockTestController::class, 'index'])->name('mock-tests.index');
        Route::get('/bo-de-thi-thu/tao-moi', [MockTestController::class, 'create'])->name('mock-tests.create');
        Route::post('/bo-de-thi-thu', [MockTestController::class, 'store'])->name('mock-tests.store');
        Route::get('/bo-de-thi-thu/{mockTest}/chinh-sua', [MockTestController::class, 'edit'])->name('mock-tests.edit');
        Route::put('/bo-de-thi-thu/{mockTest}', [MockTestController::class, 'update'])->name('mock-tests.update');
        Route::delete('/bo-de-thi-thu/{mockTest}', [MockTestController::class, 'destroy'])->name('mock-tests.destroy');
        Route::post('/bo-de-thi-thu/boc-ngau-nhien', [MockTestController::class, 'quickRandom'])->name('mock-tests.quick-random');

        // AI Soạn Câu Hỏi (Gemini)
        Route::post('/ai/tao-cau-hoi', [AiQuestionController::class, 'generate'])->name('ai.questions.generate');
        Route::post('/ai/tao-anh-cau-hoi', [AiQuestionController::class, 'generateIllustration'])->name('ai.questions.illustration');
        Route::post('/ai/don-anh-tam', [AiQuestionController::class, 'cleanupIllustrations'])->name('ai.questions.cleanup-illustrations');
        Route::post('/ai/import-cau-hoi', [AiQuestionController::class, 'import'])->name('ai.questions.import');

        // Chủ đề (Topic)
        Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
        Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
        Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');

        // Tài nguyên câu hỏi
        Route::delete('/assets/{asset}', [QuestionAssetController::class, 'destroy'])->name('assets.destroy');

        // Chương trình & Khối học
        Route::post('/programs', [AdminManagementController::class, 'storeProgram'])->name('programs.store');
        Route::put('/programs/{program}', [AdminManagementController::class, 'updateProgram'])->name('programs.update');
        Route::delete('/programs/{program}', [AdminManagementController::class, 'destroyProgram'])->name('programs.destroy');
        Route::post('/levels', [AdminManagementController::class, 'storeLevel'])->name('levels.store');
        Route::put('/levels/{level}', [AdminManagementController::class, 'updateLevel'])->name('levels.update');
        Route::delete('/levels/{level}', [AdminManagementController::class, 'destroyLevel'])->name('levels.destroy');

        // Gói dịch vụ & Đơn thuê (chỉ Admin tổng được duyệt đơn)
        Route::get('/goi-dich-vu', [AdminPackageController::class, 'index'])->name('packages.index');
        Route::post('/packages', [AdminPackageController::class, 'store'])->name('packages.store');
        Route::put('/packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{package}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/{package}/toggle', [AdminPackageController::class, 'toggle'])->name('packages.toggle');
        Route::post('/orders/{order}/activate', [AdminPackageController::class, 'activateOrder'])->name('orders.activate');
        Route::post('/orders/{order}/reject', [AdminPackageController::class, 'rejectOrder'])->name('orders.reject');

        // Live Chat hỗ trợ từ website & Telegram Bot (chứa dữ liệu của mọi khách, không mở cho giáo viên)
        Route::get('/tin-nhan/realtime-poll', [AdminController::class, 'pollSupportMessages'])->name('support.poll');
        Route::patch('/tin-nhan/{supportMessage}/trang-thai', [AdminController::class, 'updateSupportMessageStatus'])->name('support.status');
        Route::post('/tin-nhan/{supportMessage}/anh', [AdminController::class, 'uploadSupportImage'])->name('support.image');
        Route::delete('/tin-nhan/{supportMessage}', [AdminController::class, 'deleteSupportMessage'])->name('support.delete');
        Route::post('/cai-dat-telegram', [AdminController::class, 'saveTelegramConfig'])->name('telegram.save');
        Route::post('/gui-thu-telegram', [AdminController::class, 'testTelegramNotification'])->name('telegram.test');
        Route::post('/telegram/lay-chat-id', [AdminController::class, 'getTelegramChatId'])->name('telegram.get_chat_id');
        Route::post('/telegram/phan-hoi', [AdminController::class, 'sendAdminReplyViaTelegram'])->name('telegram.reply');

        // Gọi điện tư vấn qua Stringee (có ghi âm cuộc gọi)
        Route::get('/cuoc-goi/token', [SupportCallController::class, 'token'])->name('calls.token');
        Route::post('/cuoc-goi', [SupportCallController::class, 'start'])->name('calls.start');
        Route::patch('/cuoc-goi/{call}', [SupportCallController::class, 'update'])->name('calls.update');
        Route::get('/cuoc-goi/{call}/ghi-am', [SupportCallController::class, 'recording'])->name('calls.recording');
        Route::get('/tin-nhan/{supportMessage}/cuoc-goi', [SupportCallController::class, 'index'])->name('calls.index');
    });
});

// Phục vụ tài nguyên tĩnh từ storage/app/public đảm bảo hiển thị ảnh 100% trên mọi môi trường (kể cả khi symlink bị lỗi)
Route::get('/storage/{path}', function (string $path) {
    // Chỉ phục vụ file nằm đúng trong storage/app/public, chặn đường dẫn kiểu ../ để không lộ .env hay mã nguồn.
    $root = realpath(storage_path('app/public'));
    $fullPath = $root ? realpath($root . DIRECTORY_SEPARATOR . ltrim($path, '/')) : false;
    if (! $fullPath || ! str_starts_with($fullPath, $root . DIRECTORY_SEPARATOR) || ! is_file($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.file');

// Webhook Stringee (Answer URL và Event URL), xác thực bằng chữ ký trên URL, nằm dưới api/* nên không cần CSRF
Route::match(['get', 'post'], '/api/stringee/answer', [SupportCallController::class, 'answer'])->name('stringee.answer');
Route::post('/api/stringee/event', [SupportCallController::class, 'event'])->name('stringee.event');

// Tài liệu API: Swagger UI tại /docs/api, file OpenAPI tại /docs/api.json (chỉ Quản trị viên tổng; khách chưa đăng nhập được chuyển tới trang đăng nhập)
Route::get('/docs/api', fn () => view('docs.swagger'))
    ->middleware(['web', 'auth', 'can:viewApiDocs'])
    ->name('docs.swagger');
Scramble::registerJsonSpecificationRoute('docs/api.json');
Route::redirect('/docs/swagger', '/docs/api');
