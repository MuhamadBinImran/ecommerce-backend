<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the AdminSeeder to create admin user
        $this->call([
            CustomerSeeder::class,
        ]);

        // Optional: create test users
        // \App\Models\User::factory(10)->create();
    }
}
