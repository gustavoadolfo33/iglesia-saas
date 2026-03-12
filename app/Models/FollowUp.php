<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'person_id',
        'leader_id',
        'type',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'summary',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $followUp) {
            static::assignCurrentChurchOnCreate($followUp);

            if (!$followUp->created_by && auth()->id()) {
                $followUp->created_by = auth()->id();
            }
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_at', today());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotNull('due_at')
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_at', '<', now());
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function leader()
    {
        return $this->belongsTo(Leader::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
