<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MeetingType extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'name',
        'slug',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $type) {
            $user = auth()->user();

            if (!$user)
                return;

            // Tenant: forzar iglesia activa
            if ($user->isTenantUser() && $user->current_church_id) {
                $type->church_id = $user->current_church_id;
            }

            // Si no viene slug, lo generamos
            if (blank($type->slug) && filled($type->name)) {
                $type->slug = Str::slug($type->name);
            }
        });

        static::updating(function (self $type) {
            if (blank($type->slug) && filled($type->name)) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}