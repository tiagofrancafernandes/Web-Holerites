<?php

namespace App\Models;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends \Spatie\Permission\Models\Role
{
    protected $appends = [
        // 'permissionCount',
    ];

    public function getPermissionCountAttribute()
    {
        return $this->permissions()->count();
    }

    public static function clearCache()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public static function afterAction(string $action = null): void
    {
        match ($action) {
            'create' => static::afterCreate(),
            'save', 'update' => static::afterUpdate(),
            default => null,
        };

        static::clearCache();
    }

    public static function afterCreate(): void
    {
        static::syncAllPermissions();
    }

    public static function afterUpdate(): void
    {
        static::syncAllPermissions();
    }

    public static function syncAllPermissions(): void
    {
        static::clearCache();

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
    }
}
