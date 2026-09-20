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

        // Lưu vết danh sách khối lớp được hưởng tại thời điểm đặt mua
        $levelNames = $package->levels()->pluck('name')->join(', ');

        return PackageOrder::create([
            'code' => $code,
            'user_id' => $teacher->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => (int) $package->price,
            'duration_days' => (int) $package->duration_days,
            'max_students' => (int) $package->max_students,
            'levels_snapshot' => $levelNames ?: 'Toàn bộ khối',
            'order_type' => $payload['order_type'] ?? PackageOrder::TYPE_SUBSCRIPTION,
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

            $durationDays = max(1, (int) $order->duration_days);
            $currentExpiry = $teacher->expires_at;
            $isCurrentlyExpired = ! $currentExpiry || $currentExpiry->isPast();

            // 1. Cập nhật số lượng học sinh tối đa (max_students) theo chuẩn Design Logic
            if ($order->max_students > 0) {
                if ($isCurrentlyExpired || (int) $teacher->max_students === 0) {
                    // Kịch bản A: Tài khoản đã hết hạn hoặc chưa từng có gói -> Thiết lập chuẩn theo gói mới
                    $teacher->max_students = $order->max_students;
                } elseif ($order->order_type === PackageOrder::TYPE_QUOTA_ADD) {
                    // Kịch bản B: Đơn hàng mua thêm sĩ số -> Cộng dồn số lượng học sinh
                    $teacher->max_students += $order->max_students;
                } else {
                    // Kịch bản C: Đang còn hạn và gia hạn/nâng cấp gói:
                    // Nâng cấp lên nếu gói mới có sĩ số lớn hơn; nếu gói mới nhỏ hơn thì bảo lưu sĩ số hiện tại
                    if ($order->max_students > (int) $teacher->max_students) {
                        $teacher->max_students = $order->max_students;
                    }
                }
            }

            // 2. Tính toán gia hạn thời gian sử dụng (expires_at)
            if ($currentExpiry && $currentExpiry->isFuture()) {
                // Đang còn hạn: Cộng dồn thêm số ngày vào hạn hiện tại
                $teacher->expires_at = $currentExpiry->copy()->addDays($durationDays);
            } else {
                // Đã hết hạn hoặc chưa có hạn: Bắt đầu tính từ thời điểm hiện tại
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

    /**
     * Tạo đơn hàng điều chỉnh / cấp thêm sĩ số (Adjustment Order - 0đ) có liên kết FK parent_id trỏ về đơn gốc
     */
    public function createAdjustmentOrder(User $teacher, int $extraStudents, ?string $reason = null, ?User $admin = null): PackageOrder
    {
        // 1. Tìm đơn hàng đang hoạt động của giáo viên để làm đơn gốc (Đơn Cha)
        $parentOrder = $teacher->packageOrders()
            ->where('status', PackageOrder::STATUS_ACTIVE)
            ->where('order_type', PackageOrder::TYPE_SUBSCRIPTION)
            ->latest('id')
            ->first();

        $code = 'MOS-' . date('Ym') . '-ADJ' . Str::upper(Str::random(3));
        while (PackageOrder::where('code', $code)->exists()) {
            $code = 'MOS-' . date('Ym') . '-ADJ' . Str::upper(Str::random(3));
        }

        $adminName = $admin ? $admin->name : 'Ban Quản Trị';
        $noteContent = "[Điều chỉnh] {$adminName} cấp thêm +{$extraStudents} học sinh" . ($reason ? ": {$reason}" : '');

        return PackageOrder::create([
            'code' => $code,
            'user_id' => $teacher->id,
            'package_id' => $parentOrder ? $parentOrder->package_id : ($teacher->latestPackageOrder?->package_id ?? 1),
            'parent_id' => $parentOrder?->id,
            'package_name' => "Cấp thêm sĩ số (+{$extraStudents} HS) theo yêu cầu",
            'price' => 0,
            'duration_days' => 0,
            'max_students' => $extraStudents,
            'levels_snapshot' => $parentOrder?->levels_snapshot ?? 'Kèm gói chính',
            'order_type' => PackageOrder::TYPE_QUOTA_ADD,
            'status' => PackageOrder::STATUS_ACTIVE,
            'payment_method' => 'Ban Quản Trị cấp',
            'notes' => $noteContent,
            'activated_at' => Carbon::now(),
        ]);
    }
}
