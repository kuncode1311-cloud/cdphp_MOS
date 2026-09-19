<?php

namespace App\Http\Requests\Admin;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    // authorize kiểm tra ai được gửi yêu cầu; rules bên dưới kiểm tra dữ liệu gửi lên.
    public function authorize(): bool
    {
        return $this->user()?->can('create', Classroom::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $currentUser = $this->user();
        if ($currentUser && $currentUser->isTeacher()) {
            $this->merge(['teacher_id' => $currentUser->id]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:50'],
            'grade' => ['required', 'integer', 'between:1,12'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
        ];

        if ($this->user()?->isAdmin()) {
            $rules['teacher_id'] = ['nullable', 'exists:users,id'];
        }

        return $rules;
    }
}
