<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToChurch;

class Movement extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'created_by',
        'date',
        'type',
        'category_id',
        'amount',
        'description',
        'reference',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function category()
    {
        return $this->belongsTo(MovementCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (self $movement) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // Usuario que creó el registro
            if (!$movement->created_by) {
                $movement->created_by = $user->id;
            }

            // Si es usuario tenant, forzamos su iglesia activa
            if ($user->isTenantUser() && $user->current_church_id) {
                $movement->church_id = $user->current_church_id;
            }
        });
    }
}