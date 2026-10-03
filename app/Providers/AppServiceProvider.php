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
        // Dùng giao diện Swagger UI riêng cho /docs/api thay vì giao diện Stoplight mặc định của Scramble.
        Scramble::ignoreDefaultRoutes();
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

        // Đưa toàn bộ route của hệ thống vào tài liệu để demo thay Postman,
        // trừ chính trang tài liệu, file tĩnh và các đường dẫn kỹ thuật của Laravel.
        Scramble::configure()->routes(function (Route $route) {
            $uri = $route->uri();

            return ! (str_starts_with($uri, 'docs')
                || str_starts_with($uri, 'storage/')
                || str_starts_with($uri, 'up')
                || str_starts_with($uri, '_')
                || $uri === '/');
        });

        // Ép giao thức HTTPS cho toàn bộ link/form khi chạy trên môi trường production (Railway)
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
