<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy Phân Quyền Người Dùng (User Policy)
 * 
 * Kiểm soát quyền thao tác trên tài khoản người dùng:
 * - Admin: Toàn quyền quản trị tất cả tài khoản.
 * - Teacher: Chỉ được quản lý các Học sinh do chính mình tạo ra (created_by), không được sửa/xóa Admin hoặc Teacher khác.
 */
class UserPolicy
{
    /**
     * Xác định người dùng có thể xem danh sách tài khoản không
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Xác định người dùng có thể xem chi tiết tài khoản không
     */
    public function view(User $user, User $target): bool
    {
        if ($user->isAdmin() || $user->is($target)) {
            return true;
        }

        return $user->isTeacher() && $target->created_by === $user->id;
    }

    /**
     * Xác định người dùng có thể tạo tài khoản mới không
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return $user->isSubscriptionActive() && $user->hasAvailableStudentSlots();
        }

        return false;
    }

    /**
     * Xác định người dùng có thể cập nhật thông tin tài khoản không
     */
    // $user là người đang thao tác, $target là tài khoản muốn sửa.
    public function update(User $user, User $target): bool
    {
        if ($user->isAdmin() || $user->is($target)) {
            return true;
        }

        // Giáo viên chỉ được cập nhật học sinh do chính mình quản lý
        return $user->isTeacher() && $target->created_by === $user->id && $target->isStudent();
    }

    /**
     * Xác định người dùng có thể xóa tài khoản không
     */
    public function delete(User $user, User $target): bool
    {
        // Không ai được tự xóa chính mình
        if ($user->is($target)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Giáo viên chỉ được xóa học sinh do chính mình quản lý
        return $user->isTeacher() && $target->created_by === $user->id && $target->isStudent();
    }
}
