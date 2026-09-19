<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    // authorize kiểm tra ai được gửi yêu cầu; rules bên dưới kiểm tra dữ liệu gửi lên.
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetUser = $this->route('user');

        if (! $currentUser || ! $targetUser) {
            return false;
        }

        return $currentUser->can('update', $targetUser);
    }

    protected function prepareForValidation(): void
    {
        $currentUser = $this->user();
        $targetUser = $this->route('user');

        // Nếu là Giáo viên cập nhật: Giữ nguyên role student
        if ($currentUser && $currentUser->isTeacher()) {
            $this->merge(['role' => UserRole::Student->value]);
        }
    }

    public function rules(): array
    {
        $currentUser = $this->user();
        $isTeacher = $currentUser?->isTeacher() ?? false;
        $targetUser = $this->route('user');

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetUser)],
            'student_code' => ['nullable', 'string', 'max:30', Rule::unique('users', 'student_code')->ignore($targetUser)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['nullable', 'in:active,suspended,expired'],
            'created_by' => ['nullable', 'exists:users,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'level_ids' => ['nullable', 'array'],
            'level_ids.*' => ['exists:levels,id'],
        ];

        // Nếu là Giáo viên cập nhật học sinh
        if ($isTeacher) {
            $rules['classroom_id'] = [
                'nullable',
                Rule::exists('classrooms', 'id')->where('teacher_id', $currentUser->id),
            ];

            $rules['level_ids.*'] = [
                Rule::exists('teacher_level', 'level_id')->where('teacher_id', $currentUser->id),
            ];
        } else {
            // Dành cho Quản trị viên cập nhật thông tin Giáo viên
            $rules['max_students'] = ['nullable', 'integer', 'min:0'];
            $rules['expires_at'] = ['nullable', 'date'];
            $rules['teacher_level_ids'] = ['nullable', 'array'];
            $rules['teacher_level_ids.*'] = ['exists:levels,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'classroom_id.exists' => 'Lớp học được chọn không thuộc quyền quản lý của bạn.',
            'level_ids.*.exists' => 'Bạn chỉ được cấp những Khối học mà bạn đang sở hữu.',
        ];
    }
}
