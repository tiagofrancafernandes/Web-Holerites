<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use App\Models\Role;

/**
 * @property-read mixed $mainWebRole
 * @property-read mixed $mainApiRole
 * @property-read mixed $mainWebRole
 */
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasPermissions;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'status',
        'language',
        'is_canonical',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
        'is_canonical' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

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
                fn() => $this->getAllPermissions()
            ) ?: collect();
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->getPermissionsViaRoles()
            ->merge($this->permissions);
    }
}
