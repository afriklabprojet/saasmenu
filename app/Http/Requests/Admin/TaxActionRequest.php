<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TaxActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Vérifier que l'utilisateur est admin ou vendor
        return auth()->check() && in_array(auth()->user()->type, [1, 2, 4]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'min:1',
                'exists:tax,id'
            ]
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'id.required' => 'L\'ID de la taxe est requis.',
            'id.integer' => 'L\'ID de la taxe doit être un nombre entier.',
            'id.min' => 'L\'ID de la taxe doit être positif.',
            'id.exists' => 'Cette taxe n\'existe pas.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('id')) {
                // Vérifier que l'utilisateur a accès à cette taxe
                $tax = \App\Models\Tax::find($this->id);

                if ($tax && auth()->user()->type != 1) {
                    // Si ce n'est pas un super admin, vérifier ownership
                    $vendorId = auth()->user()->type == 4 ? auth()->user()->vendor_id : auth()->user()->id;

                    if ($tax->vendor_id != $vendorId) {
                        $validator->errors()->add('id', 'Vous n\'avez pas accès à cette taxe.');
                    }
                }
            }
        });
    }
}
