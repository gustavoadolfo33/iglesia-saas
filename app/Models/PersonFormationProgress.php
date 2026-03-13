<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonFormationProgress extends Model
{
    protected $table = 'person_formation_progress';

    protected $fillable = [
        'person_formation_id',
        'formation_lesson_id',
        'status',
        'completed_at',
        'reviewed_by',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function personFormation()
    {
        return $this->belongsTo(PersonFormation::class);
    }

    public function lesson()
    {
        return $this->belongsTo(FormationLesson::class, 'formation_lesson_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
