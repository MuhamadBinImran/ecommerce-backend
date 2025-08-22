<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Seller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        // Create seller role if not exists
        $role = Role::firstOrCreate(
            ['name' => 'seller'],
            ['guard_name' => 'api']
        );

        // Create a sample seller user
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Jane Seller',
                'password' => Hash::make('password123'),
            ]
        );

        // Assign seller role
        if (!$sellerUser->hasRole('seller')) {
            $sellerUser->assignRole('seller');
        }

        // Create seller profile
        Seller::firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'company_name' => 'Tech Supplies Ltd',
                'phone' => '9876543210',
                'address' => '456 Tech Street',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'postal_code' => '75000',
                'country' => 'Pakistan',
                'is_approved' => false,
                'is_blocked' => false,
            ]
        );
    }
}
