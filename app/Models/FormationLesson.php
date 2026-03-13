<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormationLesson extends Model
{
    protected $fillable = [
        'formation_track_id',
        'title',
        'slug',
        'description',
        'content_type',
        'content_body',
        'content_url',
        'sort_order',
        'estimated_minutes',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function track()
    {
        return $this->belongsTo(FormationTrack::class, 'formation_track_id');
    }

    public function progressRecords()
    {
        return $this->hasMany(PersonFormationProgress::class, 'formation_lesson_id');
    }
}
