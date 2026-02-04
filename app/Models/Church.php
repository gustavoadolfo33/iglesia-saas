<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    //
    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'status',
        'address',
        'city',
        'phone',
    ];
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
    }
}
