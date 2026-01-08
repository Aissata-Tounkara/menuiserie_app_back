<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin Menuiserie',
            'email' => 'tounkaraaissata474@gmail.com',
            'password' => 'password123', //Sera haché automatiquement vie le model User
        ]);
    }
}