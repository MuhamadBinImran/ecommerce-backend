<?php

namespace App\Interfaces;

interface AdminAuthInterface
{
    /**
     * Login the admin user
     *
     * @param array $data
     * @return array
     */
    public function login(array $data): array;

    /**
     * Logout the admin user (invalidate token/session)
     *
     * @return array
     */
    public function logout(): array;
}
