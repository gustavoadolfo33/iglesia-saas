<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonFormation extends Model
{
    protected $fillable = [
        'church_id',
        'person_id',
        'formation_track_id',
        'leader_id',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function track()
    {
        return $this->belongsTo(FormationTrack::class, 'formation_track_id');
    }

    public function leader()
    {
        return $this->belongsTo(Leader::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progress()
    {
        return $this->hasMany(PersonFormationProgress::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
