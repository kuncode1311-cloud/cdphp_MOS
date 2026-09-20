<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class SavePracticeTestRequest
 *
 * Form Request xác thực dữ liệu Bộ đề luyện tập (PracticeTest).
 */
class SavePracticeTestRequest extends FormRequest
{
    // authorize kiểm tra ai được gửi yêu cầu; rules bên dưới kiểm tra dữ liệu gửi lên.
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $test = $this->route('practiceTest') ?? $this->route('test');

        return [
            'topic_id' => ['required', 'exists:topics,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('practice_tests')->ignore($test)],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:300'],
            'pass_score' => ['nullable', 'integer', 'between:0,1000'],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'difficulty' => ['required', 'in:Cơ bản,Trung bình,Nâng cao'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'access_code' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'shuffle_questions' => $this->boolean('shuffle_questions'),
            'shuffle_options' => $this->boolean('shuffle_options'),
        ]);
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Vui lòng chọn chủ đề cho bài luyện.',
            'name.required' => 'Vui lòng nhập tên bài luyện tập.',
            'difficulty.required' => 'Vui lòng chọn mức độ khó.',
        ];
    }
}
