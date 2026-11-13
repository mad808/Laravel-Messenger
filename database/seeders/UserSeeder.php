<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'System Admin',
            'phone' => '+99362240774',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'plan' => 'pro',
        ]);

        User::create([
            'name' => 'Moderator User',
            'phone' => '+99365123456',
            'password' => Hash::make('password'),
            'role' => 'moderator',
            'plan' => 'free',
        ]);

        User::create([
            'name' => 'User One',
            'phone' => '+99364123456',
            'password' => Hash::make('password'),
            'role' => 'user',
            'plan' => 'free',
        ]);

        User::create([
            'name' => 'User Two',
            'phone' => '+99363123456',
            'password' => Hash::make('password'),
            'role' => 'user',
            'plan' => 'free',
        ]);
    }
}