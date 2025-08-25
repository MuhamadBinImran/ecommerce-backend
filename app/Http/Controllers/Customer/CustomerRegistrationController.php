<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegisterRequest;
use App\Http\Requests\ResendCustomerOtpRequest;
use App\Http\Requests\VerifyCustomerOtpRequest;
use App\Interfaces\CustomerRegistrationInterface;
use App\Services\ErrorLogService;
use Illuminate\Http\JsonResponse;

class CustomerRegistrationController extends Controller
{
    public function __construct(
        private CustomerRegistrationInterface $service,
        private ErrorLogService $errorLogger
    ) {}

    // POST /api/customers/register/request-otp
    public function requestOtp(CustomerRegisterRequest $request): JsonResponse
    {
        $resp = $this->service->requestOtp($request->validated());
        return response()->json($resp, $resp['success'] ? 200 : 422);
    }

    // POST /api/customers/register/verify
    public function verify(VerifyCustomerOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $resp = $this->service->verifyOtp($data['email'], $data['otp']);
        return response()->json($resp, $resp['success'] ? 201 : 422);
    }

    // POST /api/customers/register/resend-otp
    public function resend(ResendCustomerOtpRequest $request): JsonResponse
    {
        $resp = $this->service->resendOtp($request->validated()['email']);
        return response()->json($resp, $resp['success'] ? 200 : 422);
    }
}
