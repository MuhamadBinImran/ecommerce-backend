<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // allowed: processing -> shipped -> delivered, or cancel from processing
        return [
            'status' => 'required|string|in:processing,shipped,delivered,cancelled',
        ];
    }
}
