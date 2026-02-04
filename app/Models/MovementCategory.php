<?php

namespace App\Models;

use App\Models\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;
use App\Models\Church;
use App\Models\Movement;

class MovementCategory extends Model
{
    use BelongsToChurch;

    protected $table = 'movement_categories';

    protected $fillable = [
        'church_id',
        'type',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // Si es tenant, forzamos su iglesia activa
            if ($user->isTenantUser() && $user->current_church_id) {
                $category->church_id = $user->current_church_id;
            }
        });
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class, 'category_id');
    }
}