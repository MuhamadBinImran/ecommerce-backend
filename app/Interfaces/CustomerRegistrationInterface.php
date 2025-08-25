<?php

namespace App\Interfaces;

interface CustomerRegistrationInterface
{
    public function requestOtp(array $data): array; // start registration (send OTP)
    public function verifyOtp(string $email, string $otp): array; // create user+profile on success
    public function resendOtp(string $email): array;
}
