<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:1,2', // 1 = fixed, 2 = percentage
            'tax' => 'required|numeric|min:0',
        ];

        // For updates, make id required
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id'] = 'required|integer|exists:taxes,id';
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'name.string' => 'Le nom de la taxe doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la taxe ne peut pas dépasser 255 caractères.',
            'type.required' => 'Le type de taxe est obligatoire.',
            'type.in' => 'Le type de taxe doit être fixe (1) ou pourcentage (2).',
            'tax.required' => 'La valeur de la taxe est obligatoire.',
            'tax.numeric' => 'La valeur de la taxe doit être numérique.',
            'tax.min' => 'La valeur de la taxe ne peut pas être négative.',
            'id.required' => 'L\'ID de la taxe est obligatoire pour la mise à jour.',
            'id.integer' => 'L\'ID de la taxe doit être un entier.',
            'id.exists' => 'La taxe spécifiée n\'existe pas.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input
        if ($this->has('name')) {
            $this->merge([
                'name' => strip_tags(trim($this->input('name')))
            ]);
        }
    }
}