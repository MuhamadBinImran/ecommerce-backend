<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $user = User::inRandomOrder()->first();

        $subtotal = $this->faker->randomFloat(2, 10, 500);
        $shipping = $this->faker->randomFloat(2, 0, 20);
        $tax = round($subtotal * 0.1, 2);
        $total = $subtotal + $shipping + $tax;

        return [
            'user_id' => $user?->id,
            'seller_id' => null,
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'status' => 'processing',
            'shipping_address' => [
                'line1' => $this->faker->streetAddress,
                'city'  => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => $this->faker->country,
            ],
            'meta' => null,
        ];
    }
}
