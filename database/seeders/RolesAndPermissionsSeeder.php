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
        )?->map(fn($permissionName) => [
            'name' => $permissionName,
            'guard_name' => 'web',
            'is_canonical' => true,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ])
        ->each(fn($permissionName) => Permission::firstOrCreate([
            'name' => $permissionName,
        ], $permissionName));

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
            ->map(fn($modelClass) => str(class_basename($modelClass))->trim()->snake()->toString())
            ->filter(fn($modelClass) => filled($modelClass))
            ->map(fn($modelClass) => $suffixList->map(fn($suffix) => "{$modelClass}::{$suffix}"));

        $permissionList = $modelPermissions
                ?->flatten()
                ?->unique()
                ?->values()
                ?->map(fn($permissionName) => [
                'name' => $permissionName,
                'guard_name' => 'web',
                'is_canonical' => true,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ])
                ?->toArray();

        Permission::upsert(
            $permissionList,
            [
                'name',
            ],
            [
                'name',
                'guard_name',
                'is_canonical',
                'created_at',
                'updated_at',
            ]
        );

        // this can be done as separate statements
        // Canonical permissions
        \collect([
            'colaborador' => [
                'painel::access',
                'document::list',
                'document::view',
                'document::viewAny',
                'document_category::list',
                'document_category::view',
                'document_category::viewAny',
            ],
            'gestor' => [
                'painel::access',

                'document_category::create',
                'document_category::delete',
                'document_category::deleteAny',
                'document_category::edit',
                'document_category::editAny',
                'document_category::forceDelete',
                'document_category::forceDeleteAny',
                'document_category::reorder',
                'document_category::reorderAny',
                'document_category::restore',
                'document_category::restoreAny',
                'document_category::update',
                'document_category::updateAny',
                'document_category::view',
                'document_category::list',
                'document_category::viewAny',

                'document::create',
                'document::delete',
                'document::deleteAny',
                'document::edit',
                'document::editAny',
                'document::forceDelete',
                'document::forceDeleteAny',
                'document::list',
                'document::listAll',
                'document::publish',
                'document::reorder',
                'document::reorderAny',
                'document::restore',
                'document::restoreAny',
                'document::unpublish',
                'document::update',
                'document::updateAny',
                'document::view',
                'document::viewAny',
                'document::manage',
            ],
        ])->each(function ($rolePermissions, $roleName) use ($globalPermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'is_canonical' => true,
            ]);
            $role->syncPermissions([
                ...$rolePermissions,
                ...$globalPermissions,
            ]);
        });

        $allPermissions = Permission::all();

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'is_canonical' => true,
        ]);
        // $superAdminRole->givePermissionTo(Permission::all());
        $superAdminRole->syncPermissions($allPermissions);

        // User::where('email', 'like', 'admin@mail.com')->first()?->permissions()->sync($allPermissions);
        User::where('email', 'like', 'admin@mail.com')->first()?->roles()->sync($superAdminRole);
    }
}
