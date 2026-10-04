<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Controller Quản Lý Tài Khoản Người Dùng (User Controller)
 * 
 * Chức năng: Cấp phát tài khoản học sinh, phân lớp và quản lý quyền người dùng.
 */
class UserController extends Controller
{
    /**
     * Cấp mới tài khoản học sinh hoặc giáo viên/quản trị viên
     */
    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $currentUser = $request->user();
        // Request đã kiểm tra quyền và dữ liệu; chỉ lấy các trường vượt qua validation.
        $data = $request->validated();
        
        $levelIds = $data['level_ids'] ?? [];
        $teacherLevelIds = $data['teacher_level_ids'] ?? [];
        unset($data['level_ids'], $data['teacher_level_ids']);

        // Nếu Giáo viên tạo học sinh: Tự động gán created_by là ID của Giáo viên
        if ($currentUser && $currentUser->isTeacher()) {
            $data['created_by'] = $currentUser->id;
            $data['role'] = \App\Enums\UserRole::Student->value;
        }

        $user = User::create($data);

        // Cấp quyền Khối học cho Học sinh (chỉ cấp các khối mà Giáo viên sở hữu)
        if ($user->isStudent() && ! empty($levelIds)) {
            if ($user->created_by) {
                $teacher = User::find($user->created_by);
                if ($teacher && $teacher->isTeacher()) {
                    $allowedLevelIds = $teacher->teacherLevels()->pluck('levels.id')->map(fn($id) => (int)$id)->toArray();
                    $levelIds = array_values(array_filter($levelIds, fn($id) => in_array((int)$id, $allowedLevelIds, true)));
                }
            }
            $user->accessibleLevels()->sync($levelIds);
        }

        // Nếu Admin tạo Giáo viên: Gán danh sách Khối lớp được phép sử dụng
        if ($user->isTeacher() && ! empty($teacherLevelIds)) {
            $user->teacherLevels()->sync($teacherLevelIds);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã tạo tài khoản học sinh thành công.',
                'user' => $user->load('accessibleLevels'),
            ]);
        }

        return back()->with('ok', 'Đã tạo tài khoản thành công.');
    }

    /**
     * Cập nhật thông tin tài khoản (Họ tên, mã học sinh, mật khẩu mới, lớp, cấp độ học, gói thuê bao)
     */
    public function update(UpdateUserRequest $request, User $user, SubscriptionService $subscriptionService): JsonResponse|RedirectResponse
    {
        $currentUser = $request->user();
        $data = $request->validated();

        $levelIds = $data['level_ids'] ?? null;
        $teacherLevelIds = $data['teacher_level_ids'] ?? null;
        $grantPackageId = $data['grant_package_id'] ?? null;
        unset($data['level_ids'], $data['teacher_level_ids'], $data['grant_package_id']);

        // Để trống mật khẩu khi sửa thì giữ mật khẩu cũ.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // 🛡️ BẢO VỆ TUYỆT ĐỐI: Nếu tự sửa thông tin chính mình, tuyệt đối không cho phép đổi vai trò hoặc trạng thái
        if ($user->id === $currentUser->id) {
            unset($data['role'], $data['status']);
        }

        // Cập nhật danh sách Khối học cho Học sinh (chỉ cấp các khối mà Giáo viên sở hữu)
        // sync() thay danh sách quyền: [] là gỡ hết, còn null ở đây là không cập nhật.
        if ($levelIds !== null && $user->isStudent()) {
            $effectiveTeacherId = array_key_exists('created_by', $data) ? $data['created_by'] : $user->created_by;
            if ($effectiveTeacherId) {
                $teacher = User::find($effectiveTeacherId);
                if ($teacher && $teacher->isTeacher()) {
                    $allowedLevelIds = $teacher->teacherLevels()->pluck('levels.id')->map(fn($id) => (int)$id)->toArray();
                    $levelIds = array_values(array_filter($levelIds, fn($id) => in_array((int)$id, $allowedLevelIds, true)));
                }
            }
            $user->accessibleLevels()->sync($levelIds);
        }

        // Cập nhật danh sách Khối học cho Giáo viên (chỉ Admin)
        if ($teacherLevelIds !== null && $user->isTeacher() && $currentUser->isAdmin()) {
            $user->teacherLevels()->sync($teacherLevelIds);
        }

        // 🔄 Tự động tạo bản ghi điều chỉnh (Adjustment Order) nếu Admin tăng sĩ số cho Giáo viên
        if ($user->isTeacher() && $currentUser->isAdmin() && isset($data['max_students'])) {
            $newMax = (int) $data['max_students'];
            $oldMax = (int) $user->max_students;
            if ($newMax > $oldMax && $oldMax > 0) {
                $extra = $newMax - $oldMax;
                $subscriptionService->createAdjustmentOrder(
                    $user,
                    $extra,
                    "Admin {$currentUser->name} cấp thêm trong Quản lý người dùng",
                    $currentUser
                );
            }
        }

        $user->update($data);

        // Admin cấp gói thủ công cho học sinh mua lẻ: lưu một đơn "Ban Quản Trị cấp" để có lịch sử đối soát
        if ($grantPackageId && $currentUser->isAdmin() && $user->isStudent()) {
            $this->recordAdminGrantedPackage($user, (int) $grantPackageId, $currentUser);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã cập nhật thông tin thành công.',
                'user' => $user->fresh()->load('accessibleLevels'),
            ]);
        }

        return back()->with('ok', 'Đã cập nhật thông tin người dùng thành công.');
    }

    /**
     * Xóa tài khoản người dùng khỏi hệ thống
     */
    public function destroy(User $user): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $user);

        $userId = $user->id;
        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã xóa người dùng thành công.',
                'id' => $userId,
            ]);
        }

        return back()->with('ok', 'Đã xóa người dùng thành công.');
    }

    /**
     * Ghi nhận đơn gói do Quản trị viên cấp thủ công (giá 0 đồng) cho học sinh mua lẻ.
     */
    private function recordAdminGrantedPackage(User $student, int $packageId, User $admin): void
    {
        $package = \App\Models\Package::with('levels')->find($packageId);
        if (! $package) {
            return;
        }

        do {
            $code = 'MOS-'.date('Ym').'-ADM'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5));
        } while (\App\Models\PackageOrder::where('code', $code)->exists());

        \App\Models\PackageOrder::create([
            'code' => $code,
            'user_id' => $student->id,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'price' => 0,
            'duration_days' => (int) $package->duration_days,
            'max_students' => 1,
            'levels_snapshot' => $package->levels->pluck('name')->join(', ') ?: 'Toàn bộ khối',
            'order_type' => \App\Models\PackageOrder::TYPE_SUBSCRIPTION,
            'status' => \App\Models\PackageOrder::STATUS_ACTIVE,
            'payment_method' => 'Ban Quản Trị cấp',
            'notes' => "Quản trị viên {$admin->name} cấp thủ công trong Quản lý người dùng",
            'activated_at' => now(),
        ]);
    }
}
