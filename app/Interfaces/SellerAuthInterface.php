<?php

namespace App\Interfaces;

interface SellerAuthInterface
{
    /**
     * Register a new seller (user + seller profile).
     *
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed|null, 'error' => string|null]
     */
    public function register(array $data): array;
}
