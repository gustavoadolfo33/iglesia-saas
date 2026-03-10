<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class MeetingGroup extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group) {
            $user = auth()->user();

            if (!$user)
                return;

            // Tenant: forzar iglesia activa
            if ($user->isTenantUser() && $user->current_church_id) {
                $group->church_id = $user->current_church_id;
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