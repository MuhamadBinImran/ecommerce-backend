<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow if user is authenticated and has seller role
        return auth()->check() && auth()->user()->hasRole('seller');
    }

    public function rules(): array
    {
        return [
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:1000',
            'city'         => 'nullable|string|max:255',
            'state'        => 'nullable|string|max:255',
            'postal_code'  => 'nullable|string|max:50',
            'country'      => 'nullable|string|max:255',
        ];
    }
}
