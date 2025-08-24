<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerProfileUpdateRequest;
use App\Interfaces\SellerProfileInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SellerProfileController extends Controller
{
    private SellerProfileInterface $service;

    public function __construct(SellerProfileInterface $service)
    {
        $this->service = $service;
    }

    public function show(): JsonResponse
    {
        $userId = Auth::id();
        $response = $this->service->getProfile($userId);
        return response()->json($response, $response['success'] ? 200 : 404);
    }

    public function update(SellerProfileUpdateRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $response = $this->service->updateProfile($userId, $request->validated());
        return response()->json($response, $response['success'] ? 200 : 400);
    }
}
