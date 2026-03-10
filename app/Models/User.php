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
        return $this->hasRole('super-admin')
            || $this->isGlobalUser()
            || $this->isTenantUser();
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

    public function isGlobalUser(): bool
    {
        return $this->hasAnyRole([
            'presidente',
            'tesorero',
            'presbitero',
        ]);
    }

    public function isTenantUser(): bool
    {
        return $this->hasAnyRole([
            'pastor',
            'contador',
        ]);
    }
}