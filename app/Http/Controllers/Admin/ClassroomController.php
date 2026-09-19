<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Requests\Admin\UpdateClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;

/**
 * Controller Quản Lý Lớp Học (Classroom Controller)
 * 
 * Chức năng: Thêm, sửa, xóa lớp học trong hệ thống quản trị,
 * tự động gán giáo viên quản lý lớp.
 */
class ClassroomController extends Controller
{
    /**
     * Tạo lớp học mới (Tên lớp, khối 3/4/5, niên khóa)
     */
    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        Classroom::create($request->validated() + ['teacher_id' => $request->user()->id]);

        return back()->with('ok', 'Đã tạo lớp mới.');
    }

    /**
     * Cập nhật thông tin lớp học
     */
    public function update(UpdateClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return back()->with('ok', 'Đã cập nhật lớp.');
    }

    /**
     * Xóa lớp học
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $this->authorize('delete', $classroom);

        $classroom->delete();

        return back()->with('ok', 'Đã xóa lớp thành công.');
    }
}
