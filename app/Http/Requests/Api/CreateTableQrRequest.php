<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateTableQrRequest extends FormRequest
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
            'table_number' => 'required|integer|min:1',
            'table_name' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:20',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'table_number.required' => 'Table number is required',
            'table_number.integer' => 'Table number must be an integer',
            'table_number.min' => 'Table number must be at least 1',
            'capacity.integer' => 'Capacity must be an integer',
            'capacity.min' => 'Capacity must be at least 1',
            'capacity.max' => 'Capacity cannot exceed 20 people',
            'table_name.max' => 'Table name cannot exceed 255 characters',
            'location.max' => 'Location cannot exceed 255 characters',
            'notes.max' => 'Notes cannot exceed 500 characters',
        ];
    }
}
