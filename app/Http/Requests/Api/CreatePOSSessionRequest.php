<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreatePOSSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'terminal_id' => 'required|integer|exists:pos_terminals,id',
            'staff_name' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'terminal_id.required' => 'Terminal ID is required',
            'terminal_id.exists' => 'Selected terminal does not exist',
            'staff_name.required' => 'Staff name is required',
            'opening_balance.required' => 'Opening balance is required',
            'opening_balance.numeric' => 'Opening balance must be a number',
            'opening_balance.min' => 'Opening balance cannot be negative',
        ];
    }
}
