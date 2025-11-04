<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
            'status' => ['sometimes', 'string', Rule::in(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])],
            'payment_status' => ['sometimes', 'string', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'scheduled_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'cancellation_reason' => ['required_if:status,cancelled', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Le statut doit être: pending, confirmed, preparing, ready, completed, ou cancelled.',
            'payment_status.in' => 'Statut de paiement invalide.',
            'scheduled_at.after' => 'La date de planification doit être dans le futur.',
            'cancellation_reason.required_if' => 'La raison d\'annulation est requise.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateStatusTransition($validator);
        });
    }

    /**
     * Validate status transition is allowed
     */
    private function validateStatusTransition($validator): void
    {
        if (!$this->has('status')) {
            return;
        }

        $order = $this->route('order');
        $currentStatus = $order->status ?? 'pending';
        $newStatus = $this->input('status');

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            $validator->errors()->add('status',
                "Transition de statut non autorisée de '{$currentStatus}' vers '{$newStatus}'."
            );
        }
    }
}
