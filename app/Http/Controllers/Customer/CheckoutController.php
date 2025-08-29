<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Interfaces\OrderInterface;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    private OrderInterface $orderService;

    public function __construct(OrderInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Create an order (checkout)
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $payload = $request->only(['shipping_address', 'payment_method', 'payment_meta', 'idempotency_key']);

        try {
            $order = $this->orderService->createOrder($userId, $payload);
            return response()->json(['success' => true, 'data' => $order], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('CheckoutController@store error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * List orders for user (paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $filters = $request->only(['per_page', 'page']);
        $orders = $this->orderService->listOrders($userId, $filters);
        return response()->json(['success' => true, 'data' => $orders], 200);
    }

    /**
     * Get order detail
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $order = $this->orderService->getOrder($userId, $id);
            return response()->json(['success' => true, 'data' => $order], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
    }
}
