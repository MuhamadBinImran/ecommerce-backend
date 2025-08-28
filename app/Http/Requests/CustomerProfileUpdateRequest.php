<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Auth middleware already ensures user is authenticated and role:customer
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:255',
            'phone'       => 'sometimes|nullable|string|max:30',
            'address'     => 'sometimes|nullable|string|max:1024',
            'city'        => 'sometimes|nullable|string|max:255',
            'state'       => 'sometimes|nullable|string|max:255',
            'postal_code' => 'sometimes|nullable|string|max:50',
            'country'     => 'sometimes|nullable|string|max:255',
        ];
    }
}
