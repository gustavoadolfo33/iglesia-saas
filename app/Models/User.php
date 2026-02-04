<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_church_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
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
