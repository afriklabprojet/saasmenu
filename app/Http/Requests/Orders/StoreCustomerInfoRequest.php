<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerInfoRequest extends FormRequest
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
            'edit_type' => [
                'required',
                'string',
                Rule::in(['customer_info', 'delivery_info']),
            ],
            
            // Règles conditionnelles pour customer_info
            'customer_name' => [
                Rule::requiredIf($this->edit_type === 'customer_info'),
                'nullable',
                'string',
                'max:255',
            ],
            'customer_mobile' => [
                Rule::requiredIf($this->edit_type === 'customer_info'),
                'nullable',
                'string',
                'max:20',
            ],
            'customer_email' => [
                Rule::requiredIf($this->edit_type === 'customer_info'),
                'nullable',
                'email',
                'max:255',
            ],
            
            // Règles conditionnelles pour delivery_info
            'customer_address' => [
                Rule::requiredIf($this->edit_type === 'delivery_info'),
                'nullable',
                'string',
            ],
            'customer_building' => [
                'nullable',
                'string',
                'max:255',
            ],
            'customer_landmark' => [
                'nullable',
                'string',
                'max:255',
            ],
            'customer_pincode' => [
                'nullable',
                'string',
                'max:10',
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
            'edit_type.required' => 'The edit type is required.',
            'edit_type.in' => 'The edit type must be either "customer_info" or "delivery_info".',
            'customer_name.required' => 'The customer name is required when updating customer info.',
            'customer_mobile.required' => 'The customer mobile is required when updating customer info.',
            'customer_email.required' => 'The customer email is required when updating customer info.',
            'customer_email.email' => 'The customer email must be a valid email address.',
            'customer_address.required' => 'The customer address is required when updating delivery info.',
        ];
    }
}
