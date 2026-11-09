<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo accounts (password: password123). Admin & Technician cannot self-register.
     */
    public function run(): void
    {
        $accounts = [
            ['Admin Techcare', 'admin@techcare.test', User::ROLE_ADMIN],
            ['Technician Budi', 'teknisi@techcare.test', User::ROLE_TEKNISI],
            ['Customer Demo', 'customer@techcare.test', User::ROLE_CUSTOMER],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'password' => 'password123', 'phone' => '081234567890']
            );
        }
    }
}
