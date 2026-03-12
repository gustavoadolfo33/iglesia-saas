<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'name',
        'church_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $household) {
            $user = auth()->user();

            if ($user && $user->isTenantUser() && $user->current_church_id) {
                $household->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
