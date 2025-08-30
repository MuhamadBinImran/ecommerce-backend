<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;


class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'seller' => [
                'id' => $this->seller?->id,
                'company_name' => $this->seller?->company_name,
            ],
        ];
    }
}
