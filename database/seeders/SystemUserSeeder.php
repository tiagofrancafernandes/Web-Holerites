<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $superUsers = \collect([
            [
                'id' => '996b2511-6e07-42a2-b112-bddaaa7229fe',
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('power@123'),
                'remember_token' => Str::random(10),
            ]
        ]);

        $superUsers->each(
            function ($superUser) {
                $user = User::firstOrCreate([
                    'email' => $superUser['email'],
                ], $superUser);

                $user->syncRoles(['super-admin']);
            }
        );

        // ## https://spatie.be/docs/laravel-permission/v5/basic-usage/role-permissions#assigning-roles
        // ## Adicionando uma permissão específica à um usuário
        // $user->givePermissionTo('edit articles');

        // ## Adicionando uma role à um usuário
        // $user->assignRole('writer');

        // ## Removendo uma role de um usuário
        // $user->removeRole('writer');

        // // Todos os papéis atuais serão removidor e substituido pelos informados no array
        // $user->syncRoles(['writer', 'admin']);

        if (app()->isProduction()) {
            return;
        }

        // User::factory(5)->create();
        // User::factory(5)->unverified()->create();
    }
}
