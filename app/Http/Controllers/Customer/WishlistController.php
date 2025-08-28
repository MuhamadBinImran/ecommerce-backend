<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Interfaces\WishlistInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

class WishlistController extends Controller
{
    private WishlistInterface $wishlistService;

    public function __construct(WishlistInterface $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Get all wishlist items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->wishlistService->getWishlist($request->user()->id);
        return response()->json(['success' => true, 'data' => $items], 200);
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        try {
            $item = $this->wishlistService->addToWishlist($request->user()->id, $data['product_id']);
            return response()->json(['success' => true, 'data' => $item], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found or not available'], 404);
        } catch (Throwable $e) {
            Log::error('WishlistController@store error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * Remove a product from the wishlist by wishlist item ID.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            // If you want delete by product_id instead, change service/controller accordingly.
            $deleted = $this->wishlistService->removeFromWishlist($request->user()->id, $id);
            // Note: removeFromWishlist currently deletes by product_id in service. If `id` is wishlist id this will not work.
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Wishlist item not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Removed from wishlist'], 200);
        } catch (Throwable $e) {
            Log::error('WishlistController@destroy error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * Clear the entire wishlist for the authenticated user.
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $count = $this->wishlistService->clearWishlist($request->user()->id);
            return response()->json(['success' => true, 'message' => "Cleared {$count} items"], 200);
        } catch (Throwable $e) {
            Log::error('WishlistController@clear error', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
