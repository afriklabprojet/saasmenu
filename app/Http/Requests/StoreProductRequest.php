<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'item_name' => ['required', 'string', 'max:255'],
            'item_description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'vendor_id' => ['required', 'integer', 'exists:users,id'],
            'item_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'product_status' => ['required', 'string', Rule::in(['available', 'unavailable', 'hidden'])],
            'is_featured' => ['sometimes', 'boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:1', 'max:300'], // minutes
            'calories' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'allergens' => ['nullable', 'string', 'max:500'],
            'ingredients' => ['nullable', 'string', 'max:1000'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'spice_level' => ['nullable', 'string', Rule::in(['none', 'mild', 'medium', 'hot', 'very_hot'])],
            'dietary_info' => ['nullable', 'array'],
            'dietary_info.*' => [Rule::in(['vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'nut_free', 'halal', 'kosher'])],

            // Image validation
            'item_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // 2MB max
            'gallery_images' => ['nullable', 'array', 'max:5'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:1024'], // 1MB max each

            // Variants validation
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.is_default' => ['sometimes', 'boolean'],

            // Extras validation
            'extras' => ['nullable', 'array'],
            'extras.*.name' => ['required_with:extras', 'string', 'max:100'],
            'extras.*.price' => ['required_with:extras', 'numeric', 'min:0'],
            'extras.*.max_quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_name.required' => 'Le nom du produit est requis.',
            'item_name.max' => 'Le nom du produit ne peut dépasser 255 caractères.',
            'category_id.required' => 'La catégorie est requise.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'vendor_id.required' => 'Le restaurant est requis.',
            'vendor_id.exists' => 'Le restaurant sélectionné n\'existe pas.',
            'item_price.required' => 'Le prix est requis.',
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
            'gallery_images.*.image' => 'Tous les fichiers doivent être des images.',
            'gallery_images.*.max' => 'Chaque image de galerie ne peut dépasser 1MB.',
            'variants.*.name.required_with' => 'Le nom de la variante est requis.',
            'variants.*.price.required_with' => 'Le prix de la variante est requis.',
            'extras.*.name.required_with' => 'Le nom de l\'extra est requis.',
            'extras.*.price.required_with' => 'Le prix de l\'extra est requis.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateCategoryOwnership($validator);
            $this->validateVariantsLogic($validator);
            $this->validateExtrasLogic($validator);
        });
    }

    /**
     * Validate that category belongs to the vendor
     */
    private function validateCategoryOwnership($validator): void
    {
        $categoryId = $this->input('category_id');
        $vendorId = $this->input('vendor_id');

        if ($categoryId && $vendorId) {
            $category = \App\Models\Category::find($categoryId);
            if ($category && $category->vendor_id != $vendorId) {
                $validator->errors()->add('category_id', 'Cette catégorie n\'appartient pas à ce restaurant.');
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
        foreach ($variants as $index => $variant) {
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
