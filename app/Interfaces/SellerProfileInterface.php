<?php

namespace App\Interfaces;

interface SellerProfileInterface
{
    public function getProfile(int $userId): array;
    public function updateProfile(int $userId, array $data): array;
}
