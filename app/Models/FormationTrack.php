<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormationTrack extends Model
{
    protected $fillable = [
        'church_id',
        'scope_type',
        'scope_label',
        'name',
        'slug',
        'description',
        'category',
        'level',
        'affects_pastoral_flow',
        'is_paid',
        'price',
        'currency',
        'is_active',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'affects_pastoral_flow' => 'boolean',
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function lessons()
    {
        return $this->hasMany(FormationLesson::class)->orderBy('sort_order');
    }

    public function personFormations()
    {
        return $this->hasMany(PersonFormation::class);
    }

    public function trackLeaders()
    {
        return $this->hasMany(FormationTrackLeader::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
