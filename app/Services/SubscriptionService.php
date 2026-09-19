<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service Quản Lý Thuê Gói & Kích Hoạt Bản Quyền (SubscriptionService)
 *
 * Chịu trách nhiệm toàn bộ logic nghiệp vụ:
 * 1. Khởi tạo đơn đăng ký thuê gói bản quyền cho Giáo viên.
 * 2. Kích hoạt đơn hàng và tự động nâng cấp tài khoản Giáo viên (gia hạn, nâng quota học sinh, cấp khối).
 * 3. Hủy hoặc từ chối đơn hàng.
 */
class SubscriptionService
{
    /**
     * Tạo đơn đăng ký thuê gói mới cho Giáo viên
     */
    public function createOrder(User $teacher, Package $package, array $payload = []): PackageOrder
    {
        $code = 'MOS-' . date('Ym') . '-' . Str::upper(Str::random(5));
        while (PackageOrder::where('code', $code)->exists()) {
            $code = 'MOS-' . date('Ym') . '-' . Str::upper(Str::random(5));
        }

        return PackageOrder::create([
            'code' => $code,
            'user_id' => $teacher->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => (int) $package->price,
            'duration_days' => (int) $package->duration_days,
            'max_students' => (int) $package->max_students,
            'status' => PackageOrder::STATUS_PENDING,
            'payment_method' => $payload['payment_method'] ?? 'bank_transfer',
            'notes' => $payload['notes'] ?? null,
            'activated_at' => null,
        ]);
    }

    /**
     * Kích hoạt đơn thuê gói và tự động nâng cấp quyền hạn của Giáo viên
     */
    public function activateOrder(PackageOrder $order, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($order, $admin) {
            $order->refresh();

            if ($order->isActive()) {
                return true;
            }

            /** @var User $teacher */
            $teacher = $order->user;
            if (! $teacher) {
                return false;
            }

            // 1. Cập nhật số lượng học sinh tối đa (max_students)
            if ($order->max_students > 0) {
                // Nếu giáo viên chưa có giới hạn (0) hoặc gói mới có số học sinh cao hơn
                if ($teacher->max_students == 0 || $order->max_students > $teacher->max_students) {
                    $teacher->max_students = $order->max_students;
                }
            }

            // 2. Tính toán gia hạn thời gian sử dụng (expires_at)
            $durationDays = max(1, (int) $order->duration_days);
            $currentExpiry = $teacher->expires_at;

            if ($currentExpiry && $currentExpiry->isFuture()) {
                // Đang còn hạn: Cộng dồn thêm số ngày vào hạn hiện tại
                $teacher->expires_at = $currentExpiry->copy()->addDays($durationDays);
            } else {
                // Đã hết hạn hoặc chưa có hạn: Tính bắt đầu từ thời điểm hiện tại
                $teacher->expires_at = Carbon::now()->addDays($durationDays);
            }

            // Đảm bảo trạng thái tài khoản là hoạt động
            $teacher->status = 'active';
            $teacher->save();

            // 3. Cấp quyền các Khối lớp tương ứng của Gói
            $package = $order->package;
            if ($package) {
                $levelIds = $package->levels()->pluck('levels.id')->all();
                if (! empty($levelIds)) {
                    $teacher->teacherLevels()->syncWithoutDetaching($levelIds);
                }
            }

            // 4. Đánh dấu đơn hàng là đã kích hoạt
            $adminNote = $admin ? " (Duyệt bởi Admin: {$admin->name})" : '';
            $order->update([
                'status' => PackageOrder::STATUS_ACTIVE,
                'activated_at' => Carbon::now(),
                'notes' => trim(($order->notes ? $order->notes . "\n" : '') . 'Kích hoạt thành công' . $adminNote),
            ]);

            return true;
        });
    }

    /**
     * Từ chối / Hủy đơn đăng ký thuê gói
     */
    public function rejectOrder(PackageOrder $order, ?string $reason = null): bool
    {
        $append = $reason ? "\nLý do từ chối: {$reason}" : "\nĐã từ chối đơn hàng.";
        return $order->update([
            'status' => PackageOrder::STATUS_REJECTED,
            'notes' => trim(($order->notes ?? '') . $append),
        ]);
    }
}
