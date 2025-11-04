<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StatusChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->type, [1, 2, 4]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'status' => 'required|in:0,1'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'id.required' => 'L\'ID est obligatoire.',
            'id.integer' => 'L\'ID doit être un entier.',
            'id.min' => 'L\'ID doit être positif.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être 0 (inactif) ou 1 (actif).',
        ];
    }
}
