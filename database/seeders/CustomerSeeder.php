<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CustomerProfile;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Create customer role if not exists
        $role = Role::firstOrCreate(
            ['name' => 'customer'],
            ['guard_name' => 'api'] // important! must match your JWT guard
        );

        // Create a sample customer user
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
            ]
        );

        // Assign customer role
        if (!$customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }

        // Create profile if not exists
        CustomerProfile::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'postal_code' => '54000',
                'country' => 'Pakistan',
            ]
        );
    }
}
