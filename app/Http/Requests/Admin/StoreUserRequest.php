<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    // authorize kiểm tra ai được gửi yêu cầu; rules bên dưới kiểm tra dữ liệu gửi lên.
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->can('create', User::class);
    }

    protected function prepareForValidation(): void
    {
        $currentUser = $this->user();

        // Nếu là Giáo viên tạo: Luôn ép role là 'student'
        if ($currentUser && $currentUser->isTeacher()) {
            $this->merge(['role' => UserRole::Student->value]);
        } elseif (! $this->filled('role')) {
            $this->merge(['role' => UserRole::Student->value]);
        }

        if (! $this->filled('email') && $this->filled('student_code')) {
            $this->merge(['email' => strtolower($this->string('student_code')).'@student.ic3.local']);
        }
    }

    public function rules(): array
    {
        $currentUser = $this->user();
        $isTeacher = $currentUser?->isTeacher() ?? false;

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'student_code' => ['nullable', 'string', 'max:30', 'unique:users,student_code'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['nullable', 'in:active,suspended,expired'],
            'created_by' => ['nullable', 'exists:users,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'level_ids' => ['nullable', 'array'],
            'level_ids.*' => ['exists:levels,id'],
        ];

        // Nếu là Giáo viên tạo học sinh
        if ($isTeacher) {
            // Nhóm/Lớp gán vào phải do chính giáo viên này quản lý (hoặc để trống)
            $rules['classroom_id'] = [
                'nullable',
                Rule::exists('classrooms', 'id')->where('teacher_id', $currentUser->id),
            ];

            // Chỉ được cấp những Khối mà Admin đã cấp cho Giáo viên này
            $rules['level_ids.*'] = [
                Rule::exists('teacher_level', 'level_id')->where('teacher_id', $currentUser->id),
            ];
        } else {
            // Dành cho Quản trị viên khi tạo Giáo viên mới: Cấp thêm gói và phân quyền Khối
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
