<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vietqr,payos,cash,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
