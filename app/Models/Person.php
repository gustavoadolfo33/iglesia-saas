<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $table = 'persons';

    protected $fillable = [
        'church_id',
        'member_id',
        'assigned_leader_id',
        'status_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'birth_date',
        'origin_type',
        'source_notes',
        'visit_date',
        'conversion_date',
        'baptism_date',
        'is_new_believer',
        'needs_pastoral_care',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'visit_date' => 'date',
        'conversion_date' => 'date',
        'baptism_date' => 'date',
        'is_new_believer' => 'boolean',
        'needs_pastoral_care' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $person) {
            static::assignCurrentChurchOnCreate($person);
        });
    }

    public function scopeNeedsFollowUp(Builder $query): Builder
    {
        return $query->whereHas('followUps', function (Builder $followUps) {
            $followUps->whereIn('status', ['pending', 'overdue']);
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function formalMember()
    {
        return $this->hasOne(Member::class, 'person_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function assignedLeader()
    {
        return $this->belongsTo(Leader::class, 'assigned_leader_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function prayerRequests()
    {
        return $this->hasMany(PrayerRequest::class);
    }

    public function discipleships()
    {
        return $this->hasMany(PersonDiscipleship::class);
    }
}
