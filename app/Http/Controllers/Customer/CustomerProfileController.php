<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerProfileUpdateRequest;
use App\Services\Customer\CustomerProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    protected CustomerProfileService $service;

    public function __construct(CustomerProfileService $service)
    {
        $this->service = $service;
    }

    // GET /api/customer/profile
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => $this->service->getProfile($user),
        ]);
    }

    // PATCH /api/customer/profile
    public function update(CustomerProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->only([
            'name','phone','address','city','state','postal_code','country'
        ]);

        $updated = $this->service->updateProfile($user, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $updated,
        ]);
    }
}
