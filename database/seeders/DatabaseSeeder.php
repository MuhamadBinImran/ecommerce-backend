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
        $this->call([
            AdminSeeder::class,
            CustomerSeeder::class,
            SellerSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,// if you don’t have categories, create some

        ]);
    }

}
