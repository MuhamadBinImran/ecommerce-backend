<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'nullable|string|max:255',
            'category_id'     => 'nullable|exists:product_categories,id',
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'stock'           => 'nullable|integer|min:0',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpeg,png,jpg,gif|max:5120',

            // new field for deleting old images
            'remove_images'   => 'nullable|array',
            'remove_images.*' => 'integer|exists:product_images,id',
        ];
    }
}
