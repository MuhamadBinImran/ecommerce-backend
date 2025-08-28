<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $profile = $this->resource;
        return [
            'id' => $profile['id'],
            'name' => $profile['name'],
            'email' => $profile['email'],
            'phone' => $profile['phone'] ?? null,
            'address' => $profile['address'] ?? null,
            'city' => $profile['city'] ?? null,
            'state' => $profile['state'] ?? null,
            'postal_code' => $profile['postal_code'] ?? null,
            'country' => $profile['country'] ?? null,
        ];
    }
}
