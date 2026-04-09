<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Menuiserie',
                'email' => 'admin@menuiserie.test',
                'password' => 'Admin12345',
                'role' => 'admin',
            ],
            [
                'name' => 'Employe Menuiserie',
                'email' => 'employee@menuiserie.test',
                'password' => 'Employee12345',
                'role' => 'employee',
            ],
            [
                'name' => 'Aicha Menuiserie',
                'email' => 'aicha@menuiserie.test',
                'password' => 'Aicha12345',
                'role' => 'employee',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'role' => $userData['role'],
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
