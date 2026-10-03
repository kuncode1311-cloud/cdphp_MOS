<?php

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Tặng tài khoản học sinh mua lẻ thử nghiệm (HSLE01) một gói đang hoạt động
     * để hồ sơ có đủ gói, hạn sử dụng và lịch sử đơn. Chạy lặp lại không tạo trùng.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $student = User::where('student_code', 'HSLE01')->first();
        $package = Package::where('slug', 'goi-but-pha-hoc-sinh')->first()
            ?? Package::where('target_audience', Package::AUDIENCE_STUDENT)->where('is_active', true)->orderBy('price')->first();

        if (! $student || ! $package || $student->packageOrders()->where('status', PackageOrder::STATUS_ACTIVE)->exists()) {
            return;
        }

        $service = app(SubscriptionService::class);
        $order = $service->createOrder($student, $package, ['notes' => 'Đơn thử nghiệm cho tài khoản mua lẻ HSLE01']);
        $service->activateOrder($order);
    }

    public function down(): void
    {
        $student = User::where('student_code', 'HSLE01')->first();
        $student?->packageOrders()->where('notes', 'Đơn thử nghiệm cho tài khoản mua lẻ HSLE01')->delete();
    }
};
