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
        return [
            'topic_id' => ['required', 'exists:topics,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:300'],
            'pass_score' => ['nullable', 'integer', 'between:0,1000'],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'access_code' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $mergeData = [
            'is_published' => $this->boolean('is_published', true),
            'shuffle_questions' => $this->boolean('shuffle_questions', false),
            'shuffle_options' => $this->boolean('shuffle_options', false),
        ];

        if ($this->has('duration_minutes') && $this->input('duration_minutes') !== null && $this->input('duration_minutes') !== '') {
            $mergeData['duration_minutes'] = (int) $this->input('duration_minutes');
        }

        if ($this->has('pass_score') && $this->input('pass_score') !== null && $this->input('pass_score') !== '') {
            $mergeData['pass_score'] = (int) $this->input('pass_score');
        }

        if ($this->has('max_score') && $this->input('max_score') !== null && $this->input('max_score') !== '') {
            $mergeData['max_score'] = (int) $this->input('max_score');
        }

        $this->merge($mergeData);
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Vui lòng chọn chủ đề cho bài luyện.',
            'name.required' => 'Vui lòng nhập tên bài luyện tập.',
        ];
    }
}
