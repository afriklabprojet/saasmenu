<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status_type' => [
                'required',
                'integer',
                Rule::in([1, 2, 3, 4]), // 1=Pending, 2=Accepted, 3=Delivered, 4=Cancelled
            ],
            'status_id' => [
                'required',
                'integer',
                'exists:custom_status,id',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status_type.required' => 'The status type is required.',
            'status_type.in' => 'The status type must be one of: Pending (1), Accepted (2), Delivered (3), or Cancelled (4).',
            'status_id.required' => 'The status ID is required.',
            'status_id.exists' => 'The selected status does not exist.',
        ];
    }
}
