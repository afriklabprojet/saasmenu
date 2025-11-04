<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->type === 'vendor';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:255'],
            'category_description' => ['nullable', 'string', 'max:500'],
            'vendor_id' => ['required', 'integer', 'exists:users,id'],
            'is_available' => ['required', 'boolean'],
            'category_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'display_style' => ['nullable', 'string', Rule::in(['grid', 'list', 'carousel'])],
            'background_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_featured' => ['sometimes', 'boolean'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_items_per_order' => ['nullable', 'integer', 'min:1'],
            'preparation_time_min' => ['nullable', 'integer', 'min:1'],
            'preparation_time_max' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_name.required' => 'Le nom de la catégorie est requis.',
            'category_name.max' => 'Le nom de la catégorie ne peut dépasser 255 caractères.',
            'vendor_id.required' => 'Le restaurant est requis.',
            'vendor_id.exists' => 'Le restaurant sélectionné n\'existe pas.',
            'is_available.required' => 'Le statut de disponibilité est requis.',
            'category_image.image' => 'Le fichier doit être une image.',
            'category_image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, webp.',
            'category_image.max' => 'L\'image ne peut dépasser 2MB.',
            'sort_order.min' => 'L\'ordre de tri doit être positif.',
            'display_style.in' => 'Le style d\'affichage doit être: grid, list, ou carousel.',
            'background_color.regex' => 'La couleur de fond doit être un code hexadécimal valide.',
            'text_color.regex' => 'La couleur du texte doit être un code hexadécimal valide.',
            'min_order_amount.min' => 'Le montant minimum de commande doit être positif.',
            'max_items_per_order.min' => 'Le nombre maximum d\'articles doit être au moins 1.',
            'preparation_time_min.min' => 'Le temps de préparation minimum doit être au moins 1 minute.',
            'preparation_time_max.min' => 'Le temps de préparation maximum doit être au moins 1 minute.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVendorOwnership($validator);
            $this->validateCategoryNameUniqueness($validator);
            $this->validatePreparationTimes($validator);
        });
    }

    /**
     * Validate vendor ownership
     */
    private function validateVendorOwnership($validator): void
    {
        $vendorId = $this->input('vendor_id');

        if ($vendorId && auth()->id() !== $vendorId) {
            $validator->errors()->add('vendor_id', 'Vous ne pouvez créer des catégories que pour votre propre restaurant.');
        }
    }

    /**
     * Validate category name uniqueness for vendor
     */
    private function validateCategoryNameUniqueness($validator): void
    {
        $categoryName = $this->input('category_name');
        $vendorId = $this->input('vendor_id');

        if ($categoryName && $vendorId) {
            $exists = \App\Models\Category::where('vendor_id', $vendorId)
                ->where('category_name', $categoryName)
                ->exists();

            if ($exists) {
                $validator->errors()->add('category_name', 'Une catégorie avec ce nom existe déjà pour ce restaurant.');
            }
        }
    }

    /**
     * Validate preparation times logic
     */
    private function validatePreparationTimes($validator): void
    {
        $minTime = $this->input('preparation_time_min');
        $maxTime = $this->input('preparation_time_max');

        if ($minTime && $maxTime && $minTime > $maxTime) {
            $validator->errors()->add('preparation_time_max',
                'Le temps de préparation maximum doit être supérieur ou égal au minimum.');
        }
    }
}
