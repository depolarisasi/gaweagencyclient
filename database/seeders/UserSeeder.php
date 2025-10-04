<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gaweagency.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1, Jakarta',
            'company_name' => 'Gawe Agency',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Staff User
        User::create([
            'name' => 'Staff Developer',
            'email' => 'staff@gaweagency.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'phone' => '081234567891',
            'address' => 'Jl. Staff No. 2, Jakarta',
            'company_name' => 'Gawe Agency',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Client User 1
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '081234567892',
            'address' => 'Jl. Client No. 3, Bandung',
            'company_name' => 'PT. Example Indonesia',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Client User 2
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '081234567893',
            'address' => 'Jl. Client No. 4, Surabaya',
            'company_name' => 'CV. Smith Digital',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Client User 3 (Inactive)
        User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '081234567894',
            'address' => 'Jl. Client No. 5, Yogyakarta',
            'company_name' => 'Wilson Corp',
            'status' => 'inactive',
            'email_verified_at' => now(),
        ]);
    }
}
