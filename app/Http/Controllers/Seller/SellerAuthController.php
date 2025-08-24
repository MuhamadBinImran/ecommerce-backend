<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRegisterRequest;
use App\Interfaces\SellerAuthInterface;
use App\Services\ErrorLogService;
use Illuminate\Http\JsonResponse;

class SellerAuthController extends Controller
{
    private SellerAuthInterface $service;
    private ErrorLogService $errorLogger;

    public function __construct(SellerAuthInterface $service, ErrorLogService $errorLogger)
    {
        $this->service = $service;
        $this->errorLogger = $errorLogger;
    }

    /**
     * Register new seller (user + seller profile). Admin must approve later.
     *
     * POST /api/sellers/register
     */
    public function register(SellerRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $response = $this->service->register($data);
            return response()->json($response, $response['success'] ? 201 : 400);
        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['action' => 'seller_register']);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'ERR_SELLER_REGISTER'
            ], 500);
        }
    }
}
