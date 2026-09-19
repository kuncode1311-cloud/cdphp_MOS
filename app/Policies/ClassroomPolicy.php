<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

/**
 * Policy Phân Quyền Nhóm/Lớp Học (Classroom Policy)
 * 
 * Kiểm soát quyền thao tác trên lớp học:
 * - Admin: Toàn quyền xem, sửa, xóa bất kỳ lớp nào.
 * - Teacher: Chỉ được xem, sửa, xóa lớp/nhóm do chính mình tạo ra (teacher_id).
 */
class ClassroomPolicy
{
    /**
     * Xác định người dùng có thể xem danh sách lớp học không
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Xác định người dùng có thể xem chi tiết lớp học không
     */
    public function view(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $classroom->teacher_id === $user->id);
    }

    /**
     * Xác định người dùng có thể tạo lớp học mới không
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    /**
     * Xác định người dùng có thể cập nhật thông tin lớp học không
     */
    // So teacher_id của lớp với ID người đăng nhập để biết lớp thuộc giáo viên nào.
    public function update(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $classroom->teacher_id === $user->id);
    }

    /**
     * Xác định người dùng có thể xóa lớp học không
     */
    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $classroom->teacher_id === $user->id);
    }
}
