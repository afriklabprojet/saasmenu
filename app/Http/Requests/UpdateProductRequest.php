<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'item_name' => ['sometimes', 'string', 'max:255'],
            'item_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'item_price' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'product_status' => ['sometimes', 'string', Rule::in(['available', 'unavailable', 'hidden'])],
            'is_featured' => ['sometimes', 'boolean'],
            'preparation_time' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:300'],
            'calories' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'allergens' => ['sometimes', 'nullable', 'string', 'max:500'],
            'ingredients' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'spice_level' => ['sometimes', 'nullable', 'string', Rule::in(['none', 'mild', 'medium', 'hot', 'very_hot'])],
            'dietary_info' => ['sometimes', 'nullable', 'array'],
            'dietary_info.*' => [Rule::in(['vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'nut_free', 'halal', 'kosher'])],

            // Image validation
            'item_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'gallery_images' => ['sometimes', 'nullable', 'array', 'max:5'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:1024'],

            // Variants validation
            'variants' => ['sometimes', 'nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.is_default' => ['sometimes', 'boolean'],

            // Extras validation
            'extras' => ['sometimes', 'nullable', 'array'],
            'extras.*.name' => ['required_with:extras', 'string', 'max:100'],
            'extras.*.price' => ['required_with:extras', 'numeric', 'min:0'],
            'extras.*.max_quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_name.max' => 'Le nom du produit ne peut dépasser 255 caractères.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'item_price.min' => 'Le prix doit être positif.',
            'item_price.max' => 'Le prix ne peut dépasser 999,999.99.',
            'product_status.in' => 'Le statut doit être: available, unavailable, ou hidden.',
            'preparation_time.min' => 'Le temps de préparation minimum est 1 minute.',
            'preparation_time.max' => 'Le temps de préparation maximum est 300 minutes.',
            'calories.min' => 'Les calories doivent être positives.',
            'calories.max' => 'Les calories ne peuvent dépasser 10,000.',
            'spice_level.in' => 'Niveau d\'épice invalide.',
            'dietary_info.*.in' => 'Information diététique invalide.',
            'item_image.image' => 'Le fichier doit être une image.',
            'item_image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, webp.',
            'item_image.max' => 'L\'image ne peut dépasser 2MB.',
            'gallery_images.max' => 'Maximum 5 images dans la galerie.',
            'gallery_images.*.max' => 'Chaque image de galerie ne peut dépasser 1MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateOwnership($validator);
            if ($this->has('category_id')) {
                $this->validateCategoryOwnership($validator);
            }
            if ($this->has('variants')) {
                $this->validateVariantsLogic($validator);
            }
            if ($this->has('extras')) {
                $this->validateExtrasLogic($validator);
            }
        });
    }

    /**
     * Validate product ownership
     */
    private function validateOwnership($validator): void
    {
        $product = $this->route('product');
        if ($product && $product->vendor_id !== auth()->id()) {
            $validator->errors()->add('authorization', 'Vous n\'êtes pas autorisé à modifier ce produit.');
        }
    }

    /**
     * Validate that category belongs to the vendor
     */
    private function validateCategoryOwnership($validator): void
    {
        $categoryId = $this->input('category_id');

        if ($categoryId) {
            $category = \App\Models\Category::find($categoryId);
            if ($category && $category->vendor_id != auth()->id()) {
                $validator->errors()->add('category_id', 'Cette catégorie ne vous appartient pas.');
            }
        }
    }

    /**
     * Validate variants business logic
     */
    private function validateVariantsLogic($validator): void
    {
        $variants = $this->input('variants', []);
        if (empty($variants)) {
            return;
        }

        $defaultCount = 0;
        foreach ($variants as $variant) {
            if (isset($variant['is_default']) && $variant['is_default']) {
                $defaultCount++;
            }
        }

        if ($defaultCount > 1) {
            $validator->errors()->add('variants', 'Seulement une variante peut être définie par défaut.');
        }
    }

    /**
     * Validate extras business logic
     */
    private function validateExtrasLogic($validator): void
    {
        $extras = $this->input('extras', []);
        foreach ($extras as $index => $extra) {
            if (isset($extra['max_quantity']) && $extra['max_quantity'] > 10) {
                $validator->errors()->add("extras.{$index}.max_quantity",
                    'La quantité maximum d\'un extra ne peut dépasser 10.');
            }
        }
    }
}
