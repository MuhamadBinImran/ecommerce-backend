<?php

namespace App\Services\Seller;

use App\Interfaces\SelerOrderInterface; // <- typo guard note below
use App\Interfaces\SellerOrderInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ErrorLogService;
use Illuminate\Support\Facades\DB;
use Throwable;

class SellerOrderService implements SellerOrderInterface
{
    protected ErrorLogService $logger;

    public function __construct(ErrorLogService $logger)
    {
        $this->logger = $logger;
    }

    public function list(int $sellerId, array $filters = []): array
    {
        try {
            $q = Order::query()
                ->whereHas('items', function ($iq) use ($sellerId) {
                    $iq->where('seller_id', $sellerId);
                })
                ->with([
                    'items' => function ($iq) use ($sellerId) {
                        $iq->where('seller_id', $sellerId)
                            ->with('product:id,name'); // lightweight eager-load
                    },
                    'user:id,name,email',
                ])
                ->orderByDesc('id');

            if (isset($filters['status']) && $filters['status'] !== '') {
                $q->where('status', $filters['status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $q->where(function ($sq) use ($search) {
                    $sq->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('items', function ($iq) use ($search) {
                            $iq->where('product_name', 'like', "%{$search}%");
                        });
                });
            }

            // simple pagination (tweak as needed)
            $perPage = (int)($filters['per_page'] ?? 15);
            $orders = $q->paginate($perPage);

            return ['success' => true, 'data' => $orders];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'seller_order_list', 'seller_id' => $sellerId, 'filters' => $filters]);
            return ['success' => false, 'message' => 'Failed to fetch orders', 'error' => $e->getMessage()];
        }
    }

    public function get(int $sellerId, int $orderId): array
    {
        try {
            $order = Order::query()
                ->where('id', $orderId)
                ->whereHas('items', fn ($iq) => $iq->where('seller_id', $sellerId))
                ->with([
                    'items' => fn ($iq) => $iq->where('seller_id', $sellerId)->with('product:id,name'),
                    'user:id,name,email',
                ])
                ->first();

            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            return ['success' => true, 'data' => $order];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'seller_order_get', 'seller_id' => $sellerId, 'order_id' => $orderId]);
            return ['success' => false, 'message' => 'Failed to fetch order', 'error' => $e->getMessage()];
        }
    }

    public function updateStatus(int $sellerId, int $orderId, string $status): array
    {
        try {
            return DB::transaction(function () use ($sellerId, $orderId, $status) {
                $order = Order::query()
                    ->where('id', $orderId)
                    ->whereHas('items', fn ($iq) => $iq->where('seller_id', $sellerId))
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    return ['success' => false, 'message' => 'Order not found'];
                }

                // transition guard
                $current = $order->status;
                $allowed = [
                    'processing' => ['shipped', 'cancelled'],
                    'shipped'    => ['delivered'],
                    'delivered'  => [],           // final
                    'cancelled'  => [],           // final
                    'refunded'   => [],           // admin-driven
                    'disputed'   => [],           // admin-driven
                ];
                if (!in_array($status, $allowed[$current] ?? [], true)) {
                    return ['success' => false, 'message' => "Invalid status transition: {$current} → {$status}"];
                }

                $order->update(['status' => $status]);

                return ['success' => true, 'message' => 'Order status updated', 'data' => $order->fresh()];
            });
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'seller_order_update_status', 'seller_id' => $sellerId, 'order_id' => $orderId, 'status' => $status]);
            return ['success' => false, 'message' => 'Failed to update status', 'error' => $e->getMessage()];
        }
    }

    public function requestReturn(int $sellerId, int $orderId, ?string $reason = null): array
    {
        try {
            return DB::transaction(function () use ($sellerId, $orderId, $reason) {
                $order = Order::query()
                    ->where('id', $orderId)
                    ->whereHas('items', fn ($iq) => $iq->where('seller_id', $sellerId))
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    return ['success' => false, 'message' => 'Order not found'];
                }

                // business rule: only allow return when shipped or delivered
                if (!in_array($order->status, ['shipped','delivered'], true)) {
                    return ['success' => false, 'message' => 'Return allowed only for shipped/delivered orders'];
                }

                // mark order as "disputed" for admin review OR directly "refunded/returned" per your policy
                $meta = $order->meta ?? [];
                $meta['return_request'] = [
                    'requested_by' => 'seller',
                    'seller_reason' => $reason,
                    'requested_at' => now()->toISOString(),
                ];

                $order->update([
                    'status' => 'disputed',
                    'meta'   => $meta,
                ]);

                return ['success' => true, 'message' => 'Return requested; awaiting admin review', 'data' => $order->fresh()];
            });
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'seller_order_request_return', 'seller_id' => $sellerId, 'order_id' => $orderId, 'reason' => $reason]);
            return ['success' => false, 'message' => 'Failed to request return', 'error' => $e->getMessage()];
        }
    }
}
