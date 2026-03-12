<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class DiscipleshipTrack extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $track) {
            static::assignCurrentChurchOnCreate($track);
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function personDiscipleships()
    {
        return $this->hasMany(PersonDiscipleship::class);
    }
}
