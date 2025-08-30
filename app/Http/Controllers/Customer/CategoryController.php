<?php


namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Services\Customer\CategoryService;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    protected CategoryService $service;


    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }


// GET /api/categories
    public function index()
    {
        $categories = $this->service->index();


        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }


// GET /api/categories/{id}/products
// Query params: page, per_page, search, min_price, max_price, sort, direction
    public function products(Request $request, int $id)
    {
        $filters = $request->only(['per_page', 'page', 'search', 'min_price', 'max_price', 'sort', 'direction']);
        $result = $this->service->products($id, $filters);


        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
