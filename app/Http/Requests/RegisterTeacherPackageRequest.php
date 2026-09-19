<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTeacherPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cho phép khách vãng lai (chưa có tài khoản) đăng ký
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vietqr,payos,cash,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên của Thầy/Cô.',
            'email.required' => 'Vui lòng nhập địa chỉ email đăng nhập.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống. Thầy/Cô vui lòng đăng nhập hoặc dùng email khác.',
            'password.required' => 'Vui lòng đặt mật khẩu bảo vệ tài khoản.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'phone.required' => 'Vui lòng cung cấp số điện thoại liên hệ (Zalo).',
            'phone.regex' => 'Số điện thoại không hợp lệ (vui lòng nhập 10 chữ số, ví dụ 0912345678).',
        ];
    }
}
