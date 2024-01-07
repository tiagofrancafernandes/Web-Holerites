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
                'is_canonical' => true,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ])
            ->each(
                function ($permissionData) {
                    foreach (['web', 'api'] as $guardName) {
                        $permissionData['guard_name'] = $guardName;

                        $data = $permissionData;
                        $data['name'] = ($guardName === 'api') ? ($guardName . '::' . $data['name']) : $data['name'];

                        Permission::firstOrCreate([
                            'name' => $data['name'],
                        ], $data);
                    }
                }
            );

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
                'is_canonical' => true,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ])
                ?->toArray();

        $permissionList = value(function (array $permissionList): array {
            foreach ($permissionList as $key => $permissionData) {
                $name = trim($permissionData['name'] ?? '');
                unset($permissionList[$key]);

                foreach (['web', 'api'] as $guardName) {
                    $newKey = md5("{$guardName}{$name}");
                    $tempData = $permissionData;
                    $tempData['guard_name'] = $guardName;
                    $tempData['name'] = ($guardName === 'api') ? ($guardName . '::' . $name) : $name;
                    $permissionList[$newKey] = $tempData;
                }
            }

            return $permissionList ?? [];
        }, $permissionList);

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
        ])
            ->each(function ($rolePermissions, $roleName) use ($globalPermissions) {
                foreach (['web', 'api'] as $guardName) {
                    $role = Role::firstOrCreate([
                        'name' => ($guardName === 'api') ? ($roleName . '-' . $guardName) : $roleName,
                        'is_canonical' => true,
                        'guard_name' => $guardName,
                    ]);

                    $permissionPrefix = ($guardName === 'api') ? 'api::' : '';

                    $permissions = collect($rolePermissions)->map(
                        fn($permission) => "{$permissionPrefix}{$permission}"
                    )->values()->all();

                    $role->syncPermissions([
                        ...$permissions,
                        ...(($guardName === 'api') ? [] : $globalPermissions),
                    ]);
                }
            });

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'is_canonical' => true,
        ]);

        $superAdminRoleApi = Role::firstOrCreate([
            'name' => 'api-super-admin',
            'guard_name' => 'api',
            'is_canonical' => true,
        ]);

        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
        $superAdminRoleApi->syncPermissions(Permission::where('guard_name', 'api')->get());

        $this->call([
            SystemUserSeeder::class,
        ]);
    }
}
