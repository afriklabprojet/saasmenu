<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->type === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/', 'unique:users,mobile'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,slug'],
            'restaurant_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'cuisine_type' => ['required', 'array', 'min:1'],
            'cuisine_type.*' => ['string', 'max:50'],
            'delivery_radius' => ['required', 'numeric', 'min:1', 'max:50'], // km
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['required', 'integer', 'min:10', 'max:180'], // minutes
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:30'], // percentage
            'is_available' => ['required', 'boolean'],
            'accepts_cash' => ['sometimes', 'boolean'],
            'accepts_card' => ['sometimes', 'boolean'],
            'accepts_online' => ['sometimes', 'boolean'],

            // Images
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'], // 3MB each

            // Business hours
            'business_hours' => ['required', 'array'],
            'business_hours.*.day' => ['required', 'string', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'business_hours.*.is_open' => ['required', 'boolean'],
            'business_hours.*.open_time' => ['required_if:business_hours.*.is_open,true', 'date_format:H:i'],
            'business_hours.*.close_time' => ['required_if:business_hours.*.is_open,true', 'date_format:H:i'],

            // Contact info
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],

            // Social media
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],

            // Legal
            'business_license' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_routing_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du propriétaire est requis.',
            'email.required' => 'L\'email est requis.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'mobile.required' => 'Le numéro de téléphone est requis.',
            'mobile.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'mobile.regex' => 'Format de téléphone invalide.',
            'slug.required' => 'Le slug est requis.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'slug.alpha_dash' => 'Le slug ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'restaurant_name.required' => 'Le nom du restaurant est requis.',
            'address.required' => 'L\'adresse est requise.',
            'city.required' => 'La ville est requise.',
            'postal_code.required' => 'Le code postal est requis.',
            'country.required' => 'Le pays est requis.',
            'latitude.between' => 'La latitude doit être entre -90 et 90.',
            'longitude.between' => 'La longitude doit être entre -180 et 180.',
            'cuisine_type.required' => 'Au moins un type de cuisine est requis.',
            'cuisine_type.min' => 'Au moins un type de cuisine est requis.',
            'delivery_radius.required' => 'Le rayon de livraison est requis.',
            'delivery_radius.min' => 'Le rayon de livraison minimum est 1 km.',
            'delivery_radius.max' => 'Le rayon de livraison maximum est 50 km.',
            'delivery_fee.required' => 'Les frais de livraison sont requis.',
            'delivery_fee.min' => 'Les frais de livraison doivent être positifs.',
            'minimum_order.required' => 'Le montant minimum de commande est requis.',
            'minimum_order.min' => 'Le montant minimum de commande doit être positif.',
            'estimated_delivery_time.required' => 'Le temps de livraison estimé est requis.',
            'estimated_delivery_time.min' => 'Le temps de livraison minimum est 10 minutes.',
            'estimated_delivery_time.max' => 'Le temps de livraison maximum est 180 minutes.',
            'commission_rate.required' => 'Le taux de commission est requis.',
            'commission_rate.min' => 'Le taux de commission doit être positif.',
            'commission_rate.max' => 'Le taux de commission maximum est 30%.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne peut dépasser 2MB.',
            'cover_image.max' => 'L\'image de couverture ne peut dépasser 5MB.',
            'gallery_images.max' => 'Maximum 10 images dans la galerie.',
            'business_hours.required' => 'Les horaires d\'ouverture sont requis.',
            'business_hours.*.day.in' => 'Jour de la semaine invalide.',
            'business_hours.*.open_time.required_if' => 'L\'heure d\'ouverture est requise pour les jours ouverts.',
            'business_hours.*.close_time.required_if' => 'L\'heure de fermeture est requise pour les jours ouverts.',
            'business_hours.*.open_time.date_format' => 'Format d\'heure invalide (HH:MM).',
            'business_hours.*.close_time.date_format' => 'Format d\'heure invalide (HH:MM).',
            'contact_email.email' => 'Email de contact invalide.',
            'website.url' => 'URL du site web invalide.',
            'facebook_url.url' => 'URL Facebook invalide.',
            'instagram_url.url' => 'URL Instagram invalide.',
            'twitter_url.url' => 'URL Twitter invalide.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateBusinessHours($validator);
            $this->validatePaymentMethods($validator);
            $this->validateSlugFormat($validator);
        });
    }

    /**
     * Validate business hours logic
     */
    private function validateBusinessHours($validator): void
    {
        $businessHours = $this->input('business_hours', []);

        foreach ($businessHours as $index => $hours) {
            if (isset($hours['is_open']) && $hours['is_open']) {
                $openTime = $hours['open_time'] ?? null;
                $closeTime = $hours['close_time'] ?? null;

                if ($openTime && $closeTime && $openTime >= $closeTime) {
                    $validator->errors()->add("business_hours.{$index}.close_time",
                        'L\'heure de fermeture doit être après l\'heure d\'ouverture.');
                }
            }
        }

        // Check if at least one day is open
        $hasOpenDay = false;
        foreach ($businessHours as $hours) {
            if (isset($hours['is_open']) && $hours['is_open']) {
                $hasOpenDay = true;
                break;
            }
        }

        if (!$hasOpenDay) {
            $validator->errors()->add('business_hours', 'Au moins un jour doit être ouvert.');
        }
    }

    /**
     * Validate payment methods
     */
    private function validatePaymentMethods($validator): void
    {
        $acceptsCash = $this->input('accepts_cash', false);
        $acceptsCard = $this->input('accepts_card', false);
        $acceptsOnline = $this->input('accepts_online', false);

        if (!$acceptsCash && !$acceptsCard && !$acceptsOnline) {
            $validator->errors()->add('accepts_cash', 'Au moins une méthode de paiement doit être activée.');
        }
    }

    /**
     * Validate slug format
     */
    private function validateSlugFormat($validator): void
    {
        $slug = $this->input('slug');

        if ($slug && (strlen($slug) < 3 || strlen($slug) > 50)) {
            $validator->errors()->add('slug', 'Le slug doit contenir entre 3 et 50 caractères.');
        }

        if ($slug && in_array($slug, ['admin', 'api', 'www', 'app', 'dashboard', 'login', 'register'])) {
            $validator->errors()->add('slug', 'Ce slug est réservé et ne peut être utilisé.');
        }
    }
}
