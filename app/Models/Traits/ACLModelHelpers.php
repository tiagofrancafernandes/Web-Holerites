<?php

namespace App\Models\Traits;

trait ACLModelHelpers
{
    public function mainRole(?string $guardName = null)
    {
        return match ($guardName) {
            'web' => $this->mainWebRole(),
            'api' => $this->mainApiRole(),
            default => $this->mainWebRole(),
        };
    }

    public function mainWebRole()
    {
        return $this->roles()
            ->where('guard_name', 'web')
            ->limit(1)
            ->latest('created_at');
    }

    public function mainApiRole()
    {
        return $this->roles()
            ->where('guard_name', 'api')
            ->limit(1)
            ->latest('created_at');
    }

    public function getMainRoleAttribute()
    {
        return $this->mainRole()?->first();
    }

    public function getMainWebRoleAttribute()
    {
        return $this->mainWebRole()?->first();
    }

    public function getMainApiRoleAttribute()
    {
        return $this->mainApiRole()?->first();
    }

    public function cachedCan(array|string $permission): bool
    {
        $permission = array_filter(
            is_array($permission) ? array_values($permission) : (array) $permission,
            fn($item) => filled($item) && is_string($item) && !is_numeric($item)
        );

        return !filled($permission) ? false
            : (bool) $this->getAllCachedPermissions()
                    ?->pluck('name')
                    ?->unique()
                    ?->flip()
                    ?->has($permission);
    }

    public function cachedCanAny(array|string|null $permission, ?string ...$permissions): bool
    {
        $permissions = array_filter(
            array_merge(
                $permissions,
                is_array($permission) ? array_values($permission) : (array) $permission,
            ),
            fn($item) => filled($item) && is_string($item) && !is_numeric($item)
        );

        return !$permissions ? false : (bool) $this->getAllCachedPermissions()
                ?->pluck('name')
                ?->unique()
                ?->flip()
                ?->hasAny($permissions);
    }

    public function getAllCachedPermissionsName(?bool $updateCache = null): \Illuminate\Support\Collection
    {
        return $this->getAllCachedPermissions($updateCache)
                ?->pluck('name')
                ?->unique();
    }

    public function getAllCachedPermissions(?bool $updateCache = null): \Illuminate\Support\Collection
    {
        $cacheKey = http_build_query([
            __METHOD__,
            $this->id,
        ]);

        if ($updateCache) {
            cache()->forget($cacheKey);
        }

        return cache()
            ->remember(
                $cacheKey,
                60,
                fn() => $this->getAllMergedPermissions()
            ) ?: collect();
    }

    public function getAllMergedPermissions(): \Illuminate\Support\Collection
    {
        return $this->getPermissionsViaRoles()
            ->merge($this->permissions);
    }

    public function allPermissions(): \Illuminate\Support\Collection
    {
        return $this->getAllMergedPermissions();
    }

    public function allCachedPermissions(): \Illuminate\Support\Collection
    {
        return $this->getAllCachedPermissions();
    }
}
