<?php

namespace App\Models;

use App\Models\Concerns\AssignsChurchOnCreate;
use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Status extends Model
{
    use AssignsChurchOnCreate;
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'name',
        'slug',
        'category',
        'color',
        'sort_order',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $status) {
            static::assignCurrentChurchOnCreate($status);

            if (blank($status->slug) && filled($status->name)) {
                $status->slug = Str::slug($status->name);
            }
        });

        static::updating(function (self $status) {
            if (blank($status->slug) && filled($status->name)) {
                $status->slug = Str::slug($status->name);
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function persons()
    {
        return $this->hasMany(Person::class);
    }
}
