<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Church extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'status',
        'address',
        'city',
        'country',
        'phone',
        'email',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $church) {
            if (blank($church->slug) && filled($church->name)) {
                $church->slug = Str::slug($church->name);
            }
        });

        static::updating(function (self $church) {
            if (blank($church->slug) && filled($church->name)) {
                $church->slug = Str::slug($church->name);
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function households()
    {
        return $this->hasMany(Household::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function leaders()
    {
        return $this->hasMany(Leader::class);
    }

    public function persons()
    {
        return $this->hasMany(Person::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function prayerRequests()
    {
        return $this->hasMany(PrayerRequest::class);
    }

    public function discipleshipTracks()
    {
        return $this->hasMany(DiscipleshipTrack::class);
    }

    public function personDiscipleships()
    {
        return $this->hasMany(PersonDiscipleship::class);
    }
}
