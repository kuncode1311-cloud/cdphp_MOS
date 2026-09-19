<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SaveQuestionRequest
 *
 * Form Request theo chuẩn Laravel OOP.
 * Đóng gói toàn bộ việc xác thực dữ liệu gửi lên cho Câu hỏi (Tiêu đề, loại, điểm, mảng đáp án, file đính kèm).
 */
class SaveQuestionRequest extends FormRequest
{
    /**
     * Xác định quyền hạn của người dùng đối với request này
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Định nghĩa các quy tắc xác thực (Validation Rules)
     */
    public function rules(): array
    {
        $isCreating = $this->isMethod('post');

        return [
            'practice_test_id' => [$isCreating ? 'required' : 'nullable', 'exists:practice_tests,id'],
            'title' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:MultipleChoice,MultipleResponse,Matching,MultipleChoiceText,Hotspot,Sequence'],
            'position' => ['nullable', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
            'options' => ['nullable', 'array'],
            // Dấu * áp dụng quy tắc cho từng phần tử trong mảng lựa chọn.
            'options.*.content' => ['nullable', 'string'],
            'options.*.left' => ['nullable', 'string', 'max:1000'],
            'options.*.right' => ['nullable', 'string', 'max:1000'],
            'options.*.is_correct' => ['nullable'],
            'options.*.rect' => ['nullable', 'array'],
            'options.*.available_options' => ['nullable', 'array'],
            'options.*.available_options.*' => ['nullable', 'string', 'max:1000'],
            'options.*.correct_index' => ['nullable', 'integer', 'min:0'],
            'options.*.image_path' => ['nullable', 'string', 'max:500'],
            'options.*.image_file' => ['nullable', 'file', 'image', 'max:10240'],
            'asset_file' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,svg,gif,mp3,wav,mp4,pdf'],
            'asset_kind' => ['nullable', 'in:image,audio,video,document'],
        ];
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    /**
     * Tùy chỉnh thông báo lỗi tiếng Việt thân thiện
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập nội dung câu hỏi.',
            'type.required' => 'Vui lòng chọn dạng câu hỏi.',
            'type.in' => 'Dạng câu hỏi không hợp lệ.',
            'asset_file.max' => 'Kích thước tệp đính kèm không được vượt quá 20MB.',
            'asset_file.mimes' => 'Định dạng tệp đính kèm không được hỗ trợ.',
        ];
    }
}
