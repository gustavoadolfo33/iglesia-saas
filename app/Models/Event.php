<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'location',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $event) {
            $user = auth()->user();

            if ($user && $user->isTenantUser() && $user->current_church_id) {
                $event->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
