<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Chuẩn bị dữ liệu trước khi kiểm tra validation:
     * - Tự sinh slug từ tên nếu chưa có
     * - Tự động thiết lập max_students = 1 nếu là gói dành cho học sinh (B2C)
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if (empty($this->slug) && ! empty($this->name)) {
            $mergeData['slug'] = \Illuminate\Support\Str::slug($this->name);
        }

        $audience = $this->input('target_audience', 'teacher');
        if ($audience === 'student') {
            $mergeData['max_students'] = 1;
        } elseif (! $this->has('max_students') || $this->input('max_students') === null || $this->input('max_students') === '') {
            $mergeData['max_students'] = 35; // Mặc định 1 lớp tiêu chuẩn nếu giáo viên chưa nhập
        }

        if (! empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:packages,slug'],
            'badge' => ['nullable', 'string', 'max:50'],
            'target_audience' => ['nullable', 'string', 'in:teacher,student'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0'],
            'original_price' => ['nullable', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_students' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'features_text' => ['nullable', 'string'],
            'level_ids' => ['nullable', 'array'],
            'level_ids.*' => ['exists:levels,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên gói dịch vụ.',
            'price.required' => 'Vui lòng nhập giá gói.',
            'duration_days.required' => 'Vui lòng nhập thời hạn gói (số ngày).',
            'max_students.required' => 'Vui lòng nhập số học sinh tối đa.',
        ];
    }
}
