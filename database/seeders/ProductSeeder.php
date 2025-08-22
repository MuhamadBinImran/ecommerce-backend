<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Seller;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch seller
        $seller = Seller::first(); // assuming only one seller exists

        // Fetch categories
        $categories = ProductCategory::all();

        // Sample products
        $products = [
            [
                'name' => 'Smartphone X100',
                'description' => 'Latest 6.5-inch display smartphone with 128GB storage.',
                'price' => 699.99,
                'stock' => 50,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Men\'s Casual Shirt',
                'description' => '100% cotton slim fit shirt in multiple colors.',
                'price' => 29.99,
                'stock' => 100,
                'category' => 'Clothing',
            ],
            [
                'name' => 'Kitchen Mixer',
                'description' => '5-speed electric mixer for home baking and cooking.',
                'price' => 89.50,
                'stock' => 30,
                'category' => 'Home & Kitchen',
            ],
            [
                'name' => 'Science Fiction Book',
                'description' => 'A thrilling sci-fi adventure novel.',
                'price' => 15.00,
                'stock' => 75,
                'category' => 'Books',
            ],
        ];

        foreach ($products as $item) {
            $category = $categories->where('name', $item['category'])->first();

            Product::firstOrCreate(
                [
                    'name' => $item['name'],
                    'seller_id' => $seller->id,
                ],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'is_approved' => false, // pending for admin approval
                    'is_blocked' => false,
                ]
            );
        }
    }
}
