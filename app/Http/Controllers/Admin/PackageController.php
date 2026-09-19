<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Models\Level;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Quản Lý Gói Dịch Vụ & Đơn Thuê Gói Của Admin (Admin Package Controller)
 *
 * Chức năng:
 * 1. CRUD cấu hình các Gói dịch vụ & Bản quyền phần mềm IC3.
 * 2. Quản lý và phê duyệt các Đơn đăng ký thuê gói từ Giáo viên (1-click kích hoạt).
 * 3. Thống kê số lượng đơn và doanh thu bản quyền.
 */
class PackageController extends Controller
{
    /**
     * Bảng điều khiển Quản lý Gói & Lịch sử Đơn thuê:
     * Điều hướng liền mạch về Tab Quản trị Gói trong Trung tâm Quản trị Admin
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard') . '#tab-packages');
    }

    /**
     * Thêm mới Gói dịch vụ
     */
    public function store(StorePackageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Xử lý danh sách tính năng từ textarea (mỗi dòng 1 tính năng)
        if (! empty($data['features_text'])) {
            $lines = array_filter(array_map('trim', explode("\n", $data['features_text'])));
            $data['features'] = array_values($lines);
        }
        unset($data['features_text']);

        $levelIds = $data['level_ids'] ?? [];
        unset($data['level_ids']);

        $data['is_active'] = $request->boolean('is_active', true);

        $package = Package::create($data);

        if (! empty($levelIds)) {
            $package->levels()->sync($levelIds);
        }

        return redirect()->to(route('admin.dashboard') . '#tab-packages')
            ->with('ok', "Đã tạo gói dịch vụ \"{$package->name}\" thành công.");
    }

    /**
     * Cập nhật thông tin Gói dịch vụ
     */
    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['features_text'])) {
            $lines = array_filter(array_map('trim', explode("\n", $data['features_text'])));
            $data['features'] = array_values($lines);
            unset($data['features_text']);
        }

        $levelIds = $data['level_ids'] ?? [];
        unset($data['level_ids']);

        $data['is_active'] = $request->boolean('is_active', true);

        $package->update($data);
        $package->levels()->sync($levelIds);

        return redirect()->to(route('admin.dashboard') . '#tab-packages')
            ->with('ok', "Đã cập nhật thông tin gói \"{$package->name}\" thành công.");
    }

    /**
     * Bật / Tắt trạng thái mở bán của gói
     */
    public function toggle(Package $package): RedirectResponse
    {
        $package->update(['is_active' => ! $package->is_active]);
        $statusText = $package->is_active ? 'mở bán' : 'tạm ẩn';

        return back()->with('ok', "Đã chuyển gói \"{$package->name}\" sang trạng thái {$statusText}.");
    }

    /**
     * Xóa Gói dịch vụ
     */
    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;
        $package->delete();

        return redirect()->to(route('admin.dashboard') . '#tab-packages')
            ->with('ok', "Đã xóa gói dịch vụ \"{$name}\".");
    }

    /**
     * Phê duyệt kích hoạt đơn hàng thuê gói
     */
    public function activateOrder(PackageOrder $order, SubscriptionService $subscriptionService): RedirectResponse
    {
        $success = $subscriptionService->activateOrder($order, auth()->user());

        if ($success) {
            return back()->with('ok', "Đã duyệt và kích hoạt đơn #{$order->code} cho giáo viên {$order->user?->name}. Quota và thời hạn đã được tự động cập nhật!");
        }

        return back()->with('err', "Không thể kích hoạt đơn hàng #{$order->code}. Vui lòng kiểm tra lại tài khoản giáo viên.");
    }

    /**
     * Từ chối đơn hàng thuê gói
     */
    public function rejectOrder(Request $request, PackageOrder $order, SubscriptionService $subscriptionService): RedirectResponse
    {
        $subscriptionService->rejectOrder($order, $request->input('reason'));

        return back()->with('ok', "Đã từ chối đơn hàng #{$order->code}.");
    }
}
