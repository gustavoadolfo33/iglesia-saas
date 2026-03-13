<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class Leader extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'user_id',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $leader) {
            static::assignCurrentChurchOnCreate($leader);
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function persons()
    {
        return $this->hasMany(Person::class, 'assigned_leader_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function prayerRequests()
    {
        return $this->hasMany(PrayerRequest::class);
    }

    public function personDiscipleships()
    {
        return $this->hasMany(PersonDiscipleship::class);
    }

    public function personFormations()
    {
        return $this->hasMany(PersonFormation::class);
    }

    public function formationTrackLeaders()
    {
        return $this->hasMany(FormationTrackLeader::class);
    }
}
