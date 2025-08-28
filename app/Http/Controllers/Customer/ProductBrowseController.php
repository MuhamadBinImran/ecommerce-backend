<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\ProductBrowseService;
use Illuminate\Http\Request;

class ProductBrowseController extends Controller
{
    protected ProductBrowseService $service;

    public function __construct(ProductBrowseService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'min_price', 'max_price', 'sort', 'direction', 'per_page']);
        $products = $this->service->list($filters);
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function show($id)
    {
        $product = $this->service->detail($id);
        return response()->json(['success' => true, 'data' => $product]);
    }
}
