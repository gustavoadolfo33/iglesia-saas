<?php

namespace App\Models\Traits;

use App\Models\Traits\ChurchScope;

trait BelongsToChurch
{
    protected static function bootBelongsToChurch(): void
    {
        static::addGlobalScope(new ChurchScope);
    }
}
