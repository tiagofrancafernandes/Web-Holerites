<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => \Hash::make('power@123'),
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::updateOrCreate(
                [
                    'email' => $user['email'],
                ],
                $user
            );
        }
    }
}
