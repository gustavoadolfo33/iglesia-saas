<?php

namespace App\Models\Concerns;

trait AssignsChurchOnCreate
{
    protected static function assignCurrentChurchOnCreate(object $model): void
    {
        $user = auth()->user();

        if ($user && $user->isTenantUser() && $user->current_church_id) {
            $model->church_id = $user->current_church_id;
        }
    }
}
