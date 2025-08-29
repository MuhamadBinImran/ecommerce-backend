<?php

namespace App\Services\Customer;

use App\Interfaces\OrderInterface;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CustomerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class OrderService implements OrderInterface
{
    /**
     * Create order from user's cart
     */
    public function createOrder(int $userId, array $payload): array
    {
        $shipping = $payload['shipping_address'] ?? null;
        $paymentMethod = $payload['payment_method'] ?? 'cod';
        $paymentMeta = $payload['payment_meta'] ?? null;
        $idempotencyKey = $payload['idempotency_key'] ?? null;

        // ✅ If no shipping in request, use customer profile
        if (!$shipping) {
            $profile = CustomerProfile::with('user')->where('user_id', $userId)->first();
            if ($profile) {
                $shipping = [
                    'name'        => $profile->user->name ?? null,
                    'line1'       => $profile->address,
                    'city'        => $profile->city,
                    'state'       => $profile->state,
                    'postal_code' => $profile->postal_code,
                    'country'     => $profile->country,
                ];
            } else {
                throw new \InvalidArgumentException('Shipping address not provided and no profile found.');
            }
        }

        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty.');
        }

        // Idempotency: if provided, return existing order
        if ($idempotencyKey) {
            $existing = Order::where('meta->idempotency_key', $idempotencyKey)
                ->where('user_id', $userId)
                ->first();
            if ($existing) {
                return $existing->load('orderItems')->toArray();
            }
        }

        return DB::transaction(function () use ($userId, $cartItems, $shipping, $paymentMethod, $paymentMeta, $idempotencyKey) {

            $productIds = $cartItems->pluck('product_id')->unique()->values()->all();

            // Lock product rows for update
            $products = Product::whereIn('id', $productIds)
                ->where('is_approved', true)
                ->where('is_blocked', false)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = '0.00';
            $orderItemsData = [];

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (!$product) {
                    throw new ModelNotFoundException("Product ID {$cartItem->product_id} not found or unavailable.");
                }

                $qty = (int)$cartItem->quantity;
                if ($qty <= 0) {
                    throw new \InvalidArgumentException("Invalid quantity for product {$product->id}.");
                }

                if ($product->stock < $qty) {
                    throw new \InvalidArgumentException("Product '{$product->name}' does not have enough stock. Available: {$product->stock}.");
                }

                // price * qty (string math for precise decimals)
                $lineTotal = bcmul((string)$product->price, (string)$qty, 2);
                $subtotal = bcadd((string)$subtotal, (string)$lineTotal, 2);

                $orderItemsData[] = [
                    'product_id'   => $product->id,
                    'seller_id'    => $product->seller_id ?? null,
                    'product_name' => $product->name,
                    'price'        => $product->price,
                    'quantity'     => $qty,
                    'total'        => $lineTotal,
                    'meta'         => null,
                ];

                // decrement stock while rows are locked
                $product->stock = $product->stock - $qty;
                $product->save();
            }

            // placeholder shipping/tax — replace with your logic
            $shippingCost = 0.00;
            $tax = 0.00;
            $total = bcadd((string)$subtotal, bcadd((string)$shippingCost, (string)$tax, 2), 2);

            $orderNumber = 'ORD-' . strtoupper(Str::random(6)) . '-' . Carbon::now()->format('ymdHis');

            // Create order: pass arrays (Eloquent will cast to JSON)
            $order = Order::create([
                'user_id' => $userId,
                'seller_id' => null,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'shipping' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
                'status' => 'processing',
                'shipping_address' => $shipping ?: null,
                'meta' => [
                    'payment_method' => $paymentMethod,
                    'payment_meta' => $paymentMeta,
                    'idempotency_key' => $idempotencyKey,
                ],
            ]);

            // create order_items
            foreach ($orderItemsData as $it) {
                OrderItem::create(array_merge($it, ['order_id' => $order->id]));
            }

            // delete purchased cart items
            CartItem::where('user_id', $userId)->whereIn('product_id', $productIds)->delete();

            return $order->load('orderItems')->toArray();
        }, 5); // retry attempts for deadlocks
    }

    public function listOrders(int $userId, array $filters = []): array
    {
        $query = Order::with('orderItems')->where('user_id', $userId);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    public function getOrder(int $userId, int $orderId): array
    {
        $order = Order::with('orderItems')->where('user_id', $userId)->findOrFail($orderId);
        return $order->toArray();
    }
}
