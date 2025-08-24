<?php

namespace App\Services\Admin;

use App\Interfaces\AdminOrderInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AdminOrderService implements AdminOrderInterface
{
    private const ALLOWED_STATUSES = [
        'processing', 'shipped', 'delivered',
        'cancelled', 'refunded', 'disputed', 'returned'
    ];

    public function index(array $filters = []): array
    {
        try {
            $query = Order::with(['items.product', 'user', 'seller']);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['seller_id'])) {
                $query->where('seller_id', $filters['seller_id']);
            }
            if (!empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
            }
            if (!empty($filters['q'])) {
                $query->where('order_number', 'like', "%{$filters['q']}%");
            }

            $perPage = $filters['per_page'] ?? 15;
            $orders = $query->orderByDesc('id')->paginate($perPage);

            return $this->success('Orders retrieved successfully', $orders);
        } catch (\Throwable $e) {
            return $this->failure('Failed to retrieve orders', $e);
        }
    }

    public function show(int $orderId): array
    {
        try {
            $order = Order::with(['items.product', 'user', 'seller'])->findOrFail($orderId);
            return $this->success('Order retrieved successfully', $order);
        } catch (\Throwable $e) {
            return $this->failure("Order with ID {$orderId} not found", $e);
        }
    }

    public function updateStatus(int $orderId, string $status, array $payload = []): array
    {
        if (!$this->isValidStatus($status)) {
            return $this->failure('Invalid status provided');
        }

        try {
            return DB::transaction(function () use ($orderId, $status, $payload) {
                $order = Order::findOrFail($orderId);
                $this->appendMeta($order, 'admin_updates', [
                    'status' => $status,
                    'payload' => $payload,
                    'changed_at' => now()->toDateTimeString(),
                    'admin_id' => auth()->id(),
                ]);
                $order->update(['status' => $status]);
                return $this->success('Order status updated successfully', $order);
            });
        } catch (\Throwable $e) {
            return $this->failure("Failed to update status for order {$orderId}", $e);
        }
    }

    public function refund(int $orderId, array $payload = []): array
    {
        try {
            return DB::transaction(function () use ($orderId, $payload) {
                $order = Order::findOrFail($orderId);
                $this->appendMeta($order, 'refunds', array_merge($payload, [
                    'admin_id' => auth()->id(),
                    'at' => now()->toDateTimeString()
                ]));
                $order->update(['status' => 'refunded']);
                return $this->success('Order refunded successfully', $order);
            });
        } catch (\Throwable $e) {
            return $this->failure("Failed to refund order {$orderId}", $e);
        }
    }

    public function markDisputed(int $orderId, array $payload = []): array
    {
        return $this->updateStatus($orderId, 'disputed', $payload);
    }

    public function markReturned(int $orderId, array $payload = []): array
    {
        return $this->updateStatus($orderId, 'returned', $payload);
    }

    public function bulkUpdateStatus(array $orderIds, string $status, array $payload = []): array
    {
        if (!$this->isValidStatus($status)) {
            return $this->failure('Invalid status provided');
        }

        try {
            DB::transaction(function () use ($orderIds, $status, $payload) {
                $orders = Order::whereIn('id', $orderIds)->get();
                foreach ($orders as $order) {
                    $this->appendMeta($order, 'admin_updates', [
                        'status' => $status,
                        'payload' => $payload,
                        'changed_at' => now()->toDateTimeString(),
                        'admin_id' => auth()->id(),
                    ]);
                    $order->update(['status' => $status]);
                }
            });

            return $this->success('Bulk status update completed successfully');
        } catch (\Throwable $e) {
            return $this->failure('Failed to perform bulk status update', $e);
        }
    }

    /** ---------------- Helper Methods ---------------- */

    private function isValidStatus(string $status): bool
    {
        return in_array($status, self::ALLOWED_STATUSES, true);
    }

    private function appendMeta(Order $order, string $key, array $data): void
    {
        $meta = (array)($order->meta ?? []);
        $meta[$key][] = $data;
        $order->meta = $meta;
    }

    private function success(string $message, $data = null): array
    {
        return ['success' => true, 'message' => $message, 'data' => $data];
    }

    private function failure(string $message, \Throwable $e = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'error' => $e?->getMessage()
        ];
    }
}
