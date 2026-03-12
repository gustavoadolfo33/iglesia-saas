<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'meeting_id',
        'member_id',
        'attended',
        'recorded_at',
        'notes',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attendance) {
            $user = auth()->user();

            if ($user && $user->isTenantUser() && $user->current_church_id) {
                $attendance->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
