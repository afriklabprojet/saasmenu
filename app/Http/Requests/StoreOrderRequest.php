<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:users,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'string', Rule::in(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])],
            'order_total' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'card', 'online', 'wallet'])],
            'payment_status' => ['required', 'string', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'delivery_type' => ['required', 'string', Rule::in(['pickup', 'delivery', 'dine_in'])],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required_if:delivery_type,delivery', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],

            // Order items validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.variants' => ['nullable', 'array'],
            'items.*.extras' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vendor_id.required' => 'Le restaurant est requis.',
            'vendor_id.exists' => 'Le restaurant sélectionné n\'existe pas.',
            'status.in' => 'Le statut doit être: pending, confirmed, preparing, ready, completed, ou cancelled.',
            'order_total.required' => 'Le montant total est requis.',
            'order_total.min' => 'Le montant total doit être positif.',
            'payment_method.in' => 'Méthode de paiement invalide.',
            'payment_status.in' => 'Statut de paiement invalide.',
            'delivery_type.in' => 'Type de livraison invalide.',
            'customer_name.required' => 'Le nom du client est requis.',
            'customer_phone.required' => 'Le téléphone du client est requis.',
            'customer_phone.regex' => 'Format de téléphone invalide.',
            'delivery_address.required_if' => 'L\'adresse de livraison est requise pour les livraisons.',
            'scheduled_at.after' => 'La date de planification doit être dans le futur.',
            'items.required' => 'Au moins un article est requis.',
            'items.min' => 'Au moins un article est requis.',
            'items.*.product_id.exists' => 'Produit invalide.',
            'items.*.quantity.min' => 'La quantité minimum est 1.',
            'items.*.quantity.max' => 'La quantité maximum est 100.',
            'items.*.price.min' => 'Le prix doit être positif.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate business hours
            if ($this->has('scheduled_at')) {
                $this->validateBusinessHours($validator);
            }

            // Validate order total calculation
            $this->validateOrderTotal($validator);

            // Validate vendor is active
            $this->validateVendorStatus($validator);
        });
    }

    /**
     * Validate business hours for scheduled orders
     */
    private function validateBusinessHours($validator): void
    {
        $scheduledAt = $this->input('scheduled_at');
        if ($scheduledAt) {
            $hour = date('H', strtotime($scheduledAt));
            if ($hour < 8 || $hour > 22) {
                $validator->errors()->add('scheduled_at', 'Les commandes ne peuvent être planifiées qu\'entre 8h et 22h.');
            }
        }
    }

    /**
     * Validate order total matches items
     */
    private function validateOrderTotal($validator): void
    {
        $items = $this->input('items', []);
        $calculatedTotal = 0;

        foreach ($items as $item) {
            $calculatedTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }

        $orderTotal = $this->input('order_total', 0);
        $deliveryFee = $this->input('delivery_fee', 0);
        $taxAmount = $this->input('tax_amount', 0);
        $discountAmount = $this->input('discount_amount', 0);

        $expectedTotal = $calculatedTotal + $deliveryFee + $taxAmount - $discountAmount;

        if (abs($orderTotal - $expectedTotal) > 0.01) {
            $validator->errors()->add('order_total', 'Le montant total ne correspond pas au calcul des articles.');
        }
    }

    /**
     * Validate vendor is active and accepting orders
     */
    private function validateVendorStatus($validator): void
    {
        $vendorId = $this->input('vendor_id');
        if ($vendorId) {
            $vendor = \App\Models\User::find($vendorId);
            if (!$vendor || !$vendor->is_available) {
                $validator->errors()->add('vendor_id', 'Ce restaurant n\'accepte pas de commandes actuellement.');
            }
        }
    }
}
