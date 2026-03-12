<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class PersonDiscipleship extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'person_id',
        'leader_id',
        'discipleship_track_id',
        'stage',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $discipleship) {
            static::assignCurrentChurchOnCreate($discipleship);
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

    public function track()
    {
        return $this->belongsTo(DiscipleshipTrack::class, 'discipleship_track_id');
    }
}
