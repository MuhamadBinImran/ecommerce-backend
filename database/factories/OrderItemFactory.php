<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first();

        $price = $product?->price ?? $this->faker->randomFloat(2, 5, 100);
        $qty = $this->faker->numberBetween(1, 3);

        return [
            'product_id' => $product?->id,
            'seller_id' => $product?->seller_id,
            'product_name' => $product?->name ?? $this->faker->word,
            'price' => $price,
            'quantity' => $qty,
            'total' => round($price * $qty, 2),
            'meta' => null,
        ];
    }
}
