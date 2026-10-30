<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@niralagam.com'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@niralagam.com',
                'password' => Hash::make('niralagam2024'),
                'role'     => 'super_admin',
            ]
        );
    }
}
