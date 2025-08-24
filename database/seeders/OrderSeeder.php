<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();
        $products = Product::with('seller')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            // fallback: use any users/products if roles/seeders not present
            $customers = User::all();
        }

        if ($customers->isEmpty() || $products->isEmpty()) {
            // Nothing to seed
            return;
        }

        // create 15 orders
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            // pick 1-4 random products
            $items = $products->shuffle()->take(rand(1, 4));
            $subtotal = 0;
            $shipping = rand(0, 2000) / 100; // small shipping
            $tax = 0;

            $order = Order::create([
                'user_id' => $customer->id,
                'seller_id' => $items->first()->seller_id ?? null,
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'subtotal' => 0,
                'shipping' => $shipping,
                'tax' => 0,
                'total' => 0,
                'status' => 'processing',
                'shipping_address' => [
                    'line1' => $customer->customerProfile->address ?? 'N/A',
                    'city' => $customer->customerProfile->city ?? null,
                    'postal_code' => $customer->customerProfile->postal_code ?? null,
                    'country' => $customer->customerProfile->country ?? null,
                ],
                'meta' => null,
            ]);

            foreach ($items as $p) {
                $quantity = rand(1, 3);
                $lineTotal = round(($p->price ?? 0) * $quantity, 2);
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'seller_id' => $p->seller_id,
                    'product_name' => $p->name,
                    'price' => $p->price,
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                    'meta' => null,
                ]);
            }

            $tax = round($subtotal * 0.1, 2);
            $total = round($subtotal + $tax + $shipping, 2);

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);
        }
    }
}
