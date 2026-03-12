<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public const GLOBAL_ROLES = [
        'super-admin',
        'presidente',
        'vicepresidente',
        'presbitero',
        'tesorero-global',
    ];

    public const LOCAL_ROLES = [
        'pastor',
        'contador-local',
        'encargado-reuniones',
        'encargado-seguimiento',
        'secretario-registro',
        'discipulador',
    ];

    public const PASTOR_ASSIGNABLE_LOCAL_ROLES = [
        'contador-local',
        'encargado-reuniones',
        'encargado-seguimiento',
        'secretario-registro',
        'discipulador',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_church_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(static::panelRoleNames());
    }

    public function churches()
    {
        return $this->belongsToMany(\App\Models\Church::class)
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
    }

    public function currentChurch()
    {
        return $this->belongsTo(\App\Models\Church::class, 'current_church_id');
    }

    public function leaderProfiles()
    {
        return $this->hasMany(Leader::class);
    }

    public function createdFollowUps()
    {
        return $this->hasMany(FollowUp::class, 'created_by');
    }

    public function isGlobalUser(): bool
    {
        return $this->hasAnyRole(array_values(array_diff(static::GLOBAL_ROLES, ['super-admin'])));
    }

    public function isTenantUser(): bool
    {
        return $this->hasAnyRole(static::LOCAL_ROLES);
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente', 'pastor']);
    }

    public function canCreateChurches(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente']);
    }

    public static function panelRoleNames(): array
    {
        return array_values(array_unique([
            ...static::GLOBAL_ROLES,
            ...static::LOCAL_ROLES,
        ]));
    }

    public static function assignableRoleNamesFor(?self $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->hasRole('super-admin')) {
            return static::panelRoleNames();
        }

        if ($user->hasAnyRole(['presidente', 'vicepresidente'])) {
            return array_values(array_diff(static::panelRoleNames(), ['super-admin']));
        }

        if ($user->hasRole('pastor')) {
            return static::PASTOR_ASSIGNABLE_LOCAL_ROLES;
        }

        return [];
    }
}
