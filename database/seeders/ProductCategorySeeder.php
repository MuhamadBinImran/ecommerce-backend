<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
public function run(): void
{
$categories = [
['name' => 'Electronics', 'description' => 'Gadgets and devices'],
['name' => 'Clothing', 'description' => 'Apparel and fashion'],
['name' => 'Home & Kitchen', 'description' => 'Household items'],
['name' => 'Books', 'description' => 'Printed and digital books'],
];

foreach ($categories as $category) {
ProductCategory::updateOrCreate(['name' => $category['name']], $category);
}
}
}
