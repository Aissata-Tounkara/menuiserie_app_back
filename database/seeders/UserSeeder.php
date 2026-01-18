<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tounkaraaissata474@gmail.com'], // clé unique
            [
                'name' => 'Admin Menuiserie',
                'password' => 'Password123'
            ]
        );
    }
}
