<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'person_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $member) {
            $user = auth()->user();

            if ($user && $user->isTenantUser() && $user->current_church_id) {
                $member->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
