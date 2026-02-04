<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ChurchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        if ($user->isGlobalUser()) {
            return;
        }

        if ($user->isTenantUser() && $user->current_church_id) {
            $builder->where($model->getTable() . '.church_id', $user->current_church_id);
        }
    }
}