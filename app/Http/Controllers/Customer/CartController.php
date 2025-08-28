<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Interfaces\CartInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

class CartController extends Controller
{
    private CartInterface $cartService;

    public function __construct(CartInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $cartItems = $this->cartService->getCart($request->user()->id);
            return response()->json(['success' => true, 'data' => $cartItems], 200);
        } catch (Throwable $e) {
            Log::error('CartController@index error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->addToCart($request->user()->id, $data['product_id'], $data['quantity']);
            return response()->json(['success' => true, 'data' => $item], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found or not available'], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('CartController@store error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->updateCartItem($request->user()->id, $productId, $data['quantity']);
            return response()->json(['success' => true, 'data' => $item], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('CartController@update error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        try {
            $deleted = $this->cartService->removeCartItem($request->user()->id, $productId);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Item not found or already removed'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Item removed'], 200);
        } catch (Throwable $e) {
            Log::error('CartController@destroy error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
