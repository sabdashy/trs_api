<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table (master data).
     */
    public function run(): void
    {
        // Isian sesuai form: name, email, password, role (owner / finance)
        $users = [
            [
                'name' => 'finance',
                'email' => 'finance@gmail.com',
                'password' => 'mypassword',
                'role' => 'finance', // owner | finance
            ],
            [
                'name' => 'owner',
                'email' => 'owner@gmail.com',
                'password' => 'mypassword',
                'role' => 'owner',
            ],
            [
                'name' => 'sys',
                'email' => 'sys@gmail.com',
                'password' => 'mypassword',
                'role' => 'owner',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
