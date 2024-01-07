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
}
