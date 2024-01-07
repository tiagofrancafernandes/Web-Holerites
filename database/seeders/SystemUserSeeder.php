<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;
use App\Models\Role;

class SystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initialUsers = collect([
            [
                // 'id' => '996b2511-6e07-42a2-b112-bddaaa7229fe',
                // 'remember_token' => Str::random(10),
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'email_verified_at' => now(),
                'password' => 'power@123',
                'language' => config('app.locale'),
                'is_canonical' => true,
                'roles' => [
                    'super-admin',
                    'api-super-admin',
                ],
                'permissions' => [ // Granular permission
                    //
                ],
            ]
        ]);

        if (!app()->isProduction()) {
            // Has no reason to populate demo data on prodution

            $initialUsers->push(
                [
                    // 'remember_token' => Str::random(10),
                    'name' => 'Gestor Demo',
                    'email' => 'gestor@mail.com',
                    'email_verified_at' => now(),
                    'password' => 'power@123',
                    'language' => config('app.locale'),
                    'is_canonical' => true,
                    'roles' => [
                        'gestor',
                        // 'gestor-api', // Only for API use (optional)
                    ],
                    'permissions' => [ // Granular permission
                        //
                    ],
                ],
                [
                    // 'remember_token' => Str::random(10),
                    'name' => 'Colaborador Demo',
                    'email' => 'colaborador@mail.com',
                    'email_verified_at' => now(),
                    'password' => 'power@123',
                    'language' => config('app.locale'),
                    'is_canonical' => true,
                    'roles' => [
                        'colaborador',
                        // 'colaborador-api', // Only for API use (optional)
                    ],
                    'permissions' => [ // Granular permission
                        //
                    ],
                ],
            );
        }

        $initialUsers->each(
            function ($userData) {
                $userData['password'] = Hash::make($userData['password'] ?? 'power@123');
                $userData = collect($userData);

                $user = User::firstOrCreate(
                    [
                        'email' => $userData->get('email'),
                    ],
                    $userData->only([
                        'name',
                        'email',
                        'email_verified_at',
                        'password',
                        'language',
                        'is_canonical',
                    ])->toArray()
                );

                $user->roles()->sync(
                    Role::select('id')
                        ->whereIn(
                            'name',
                            $userData
                                ->only('roles')->flatMap(fn($item) => $item)->toArray()
                        )
                        ->get()
                );

                $user->permissions()->sync(
                    Permission::select('id')
                        ->whereIn(
                            'name',
                            $userData
                                ->only('permissions')->flatMap(fn($item) => $item)->toArray()
                        )
                        ->get()
                );
            }
        );

        $this->finalInfoTable([
            'initialUsers' => $initialUsers,
        ]);
    }

    public function finalInfoTable(array $info = [])
    {
        $initialUsers = collect($info['initialUsers'] ?? []);

        $this->command->newLine(2);
        $this->command->table(
            ['Name', 'E-mail', 'Password', 'Roles'],
            $initialUsers->map(fn($item) => [
                $item['name'] ?? null,
                $item['email'] ?? null,
                '<question>' . ($item['password'] ?? null) . '</question>',
                implode(', ', $item['roles'] ?? []),
            ])->toArray()
        );
        $this->command->newLine(2);
    }
}
