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

        // Cấp quyền Khối học cho Học sinh
        if ($user->isStudent() && ! empty($levelIds)) {
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
        unset($data['level_ids'], $data['teacher_level_ids']);

        // Để trống mật khẩu khi sửa thì giữ mật khẩu cũ.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Cập nhật danh sách Khối học cho Học sinh
        // sync() thay danh sách quyền: [] là gỡ hết, còn null ở đây là không cập nhật.
        if ($levelIds !== null && $user->isStudent()) {
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
}
