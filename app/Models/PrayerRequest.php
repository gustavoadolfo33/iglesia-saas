<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'person_id',
        'leader_id',
        'title',
        'request',
        'status',
        'is_confidential',
        'requested_at',
        'answered_at',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'requested_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $prayerRequest) {
            static::assignCurrentChurchOnCreate($prayerRequest);
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function leader()
    {
        return $this->belongsTo(Leader::class);
    }
}
