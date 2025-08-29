<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // auth middleware ensures user is authenticated
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'sometimes|array', // make optional
            'shipping_address.name' => 'sometimes|string|max:255',
            'shipping_address.line1' => 'sometimes|string|max:1024',
            'shipping_address.city' => 'sometimes|string|max:255',
            'shipping_address.state' => 'nullable|string|max:255',
            'shipping_address.postal_code' => 'nullable|string|max:50',
            'shipping_address.country' => 'sometimes|string|max:255',

            'payment_method' => 'required|string|in:card,cod',
            'payment_meta' => 'sometimes|array',
            'idempotency_key' => 'sometimes|string',
        ];
    }

    protected function prepareForValidation()
    {
        // Accept idempotency key either in body or header
        if (!$this->has('idempotency_key') && $this->header('Idempotency-Key')) {
            $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
        }
    }
}
