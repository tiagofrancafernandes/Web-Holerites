<?php

namespace Database\Seeders;

## https://spatie.be/docs/laravel-permission/v3/advanced-usage/seeding

use App\Models\Author;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use App\Models\City;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\StorageFile;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        Permission::query()->forceDelete();
        Role::query()->forceDelete();
        $now = now();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        \collect(
            [
                ...Arr::flatten(
                    (array) config('permission-list', [])
                ),
                ...[
                    //
                ],
            ]
        )->each(fn ($permissionName) => Permission::firstOrCreate(['name' => $permissionName]));

        // create roles and assign created permissions

        $globalPermissions = array_values((array) config('permission-list.global_permissions'));

        $suffixList = collect(config('permission-list.default_suffix', []));

        $modelPermissions = collect([
            City::class,
            User::class,
            Role::class,
            Permission::class,
            City::class,
            Document::class,
            DocumentCategory::class,
            Permission::class,
            Role::class,
            StorageFile::class,
            User::class,
        ])
            ->unique()
            ->map(fn ($modelClass) => str(class_basename($modelClass))->trim()->snake()->toString())
            ->filter(fn ($modelClass) => filled($modelClass))
            ->map(fn ($modelClass) => $suffixList->map(fn ($suffix) => "{$modelClass}::{$suffix}"));

        $permissionList = $modelPermissions
            ?->flatten()
            ?->unique()
            ?->values()
            ?->map(fn ($permissionName) => [
                'name' => $permissionName,
                'guard_name' => 'web',
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ])
            ?->toArray();

        Permission::upsert(
            $permissionList,
            ['name'],
            [
                'name',
                'guard_name',
            ]
        );

        // this can be done as separate statements
        \collect([
            'writer' => [
                'article::edit',
                'painel::access',
            ],
            'moderator' => [
                'article::publish',
                'article::unpublish',
                'painel::access',
            ],
        ])->each(function ($rolePermissions, $roleName) use ($globalPermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions([
                ...$rolePermissions,
                ...$globalPermissions,
            ]);
        });

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        // $superAdminRole->givePermissionTo(Permission::all());
        $superAdminRole->syncPermissions(Permission::all());
    }
}
