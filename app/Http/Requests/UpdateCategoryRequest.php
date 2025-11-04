<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');
        return auth()->check() &&
               auth()->user()->type === 'vendor' &&
               $category &&
               $category->vendor_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id ?? null;

        return [
            'category_name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('vendor_id', auth()->id());
                })->ignore($categoryId)
            ],
            'category_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_available' => ['sometimes', 'boolean'],
            'category_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'display_style' => ['sometimes', 'nullable', 'string', Rule::in(['grid', 'list', 'carousel'])],
            'background_color' => ['sometimes', 'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['sometimes', 'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_featured' => ['sometimes', 'boolean'],
            'min_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_items_per_order' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'preparation_time_min' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'preparation_time_max' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_name.max' => 'Le nom de la catégorie ne peut dépasser 255 caractères.',
            'category_name.unique' => 'Une catégorie avec ce nom existe déjà pour votre restaurant.',
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
            $this->validatePreparationTimes($validator);
            $this->validateCategoryHasProducts($validator);
        });
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

    /**
     * Validate if category can be disabled when it has active products
     */
    private function validateCategoryHasProducts($validator): void
    {
        if ($this->has('is_available') && !$this->input('is_available')) {
            $category = $this->route('category');

            if ($category) {
                $hasActiveProducts = Item::where('category_id', $category->id)
                    ->where('is_available', 1)
                    ->exists();

                if ($hasActiveProducts) {
                    $validator->errors()->add('is_available',
                        'Cette catégorie ne peut être désactivée car elle contient des produits actifs. Désactivez d\'abord les produits.');
                }
            }
        }
    }
}
