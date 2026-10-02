<?php

namespace App\Providers;

use App\Models\Classroom;
use App\Models\Question;
use App\Models\User;
use App\Observers\QuestionObserver;
use App\Policies\ClassroomPolicy;
use App\Policies\UserPolicy;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký service dùng chung nếu cần; hiện chưa có đăng ký riêng.
     */
    public function register(): void
    {
        //
    }

    /**
     * Gắn các quy tắc và sự kiện khi ứng dụng khởi động.
     */
    public function boot(): void
    {
        // Nối model với policy: can()/authorize() kiểm tra quy tắc ở lớp tương ứng.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Classroom::class, ClassroomPolicy::class);

        // Tạo hoặc xóa câu hỏi qua model sẽ gọi observer để đếm lại số câu của bộ đề.
        Question::observe(QuestionObserver::class);

        // Tài liệu API (Swagger/OpenAPI) tại /docs/api: chỉ Quản trị viên tổng được xem.
        Gate::define('viewApiDocs', fn (?User $user) => $user?->isAdmin() === true);

        // Chỉ đưa vào tài liệu các route JSON/webhook dùng cho Live Chat, gọi điện, thanh toán và Telegram.
        Scramble::configure()->routes(function (Route $route) {
            $uri = $route->uri();

            return str_starts_with($uri, 'api/')
                || str_starts_with($uri, 'ho-tro/')
                || str_starts_with($uri, 'quan-tri/cuoc-goi')
                || str_starts_with($uri, 'quan-tri/tin-nhan')
                || str_starts_with($uri, 'quan-tri/telegram')
                || $uri === 'bang-gia/payos-webhook';
        });

        // Ép giao thức HTTPS cho toàn bộ link/form khi chạy trên môi trường production (Railway)
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
