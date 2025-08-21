<?php

namespace App\Interfaces;

interface AdminUserInterface
{
    /**
     * Retrieve all customers (users with 'customer' role).
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function getAllCustomers(): mixed;

    /**
     * Retrieve a single customer by their ID.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function getUserById(int $id): mixed;
}
