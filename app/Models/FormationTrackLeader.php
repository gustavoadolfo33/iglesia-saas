<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormationTrackLeader extends Model
{
    protected $fillable = [
        'formation_track_id',
        'leader_id',
        'role',
    ];

    public function track()
    {
        return $this->belongsTo(FormationTrack::class, 'formation_track_id');
    }

    public function leader()
    {
        return $this->belongsTo(Leader::class);
    }
}
