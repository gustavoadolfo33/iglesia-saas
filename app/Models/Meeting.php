<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'meeting_type_id',
        'meeting_group_id',
        'date',
        'start_time',
        'end_time',
        'topic',
        'bible_reference',
        'leader_name',
        'guest',
        'attendees_count',
        'visitors_count',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $meeting) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // created_by automático
            if (!$meeting->created_by) {
                $meeting->created_by = $user->id;
            }

            // Tenant: forzar iglesia activa
            if ($user->isTenantUser() && $user->current_church_id) {
                $meeting->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function type()
    {
        return $this->belongsTo(MeetingType::class, 'meeting_type_id');
    }

    public function group()
    {
        return $this->belongsTo(MeetingGroup::class, 'meeting_group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
