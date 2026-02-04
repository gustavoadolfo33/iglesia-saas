<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToChurch;

class Offering extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'amount',
        'type',
        'notes',
        'date',
    ];
}