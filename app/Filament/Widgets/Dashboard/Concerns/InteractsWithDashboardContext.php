<?php

namespace App\Filament\Widgets\Dashboard\Concerns;

use App\Support\Dashboard\DashboardContext;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardContext
{
    protected function getDashboardContext(): array
    {
        return DashboardContext::resolve();
    }

    protected function applyChurchFilter(Builder $query, string $column = 'church_id'): Builder
    {
        $churchId = $this->getDashboardContext()['church_id'];

        return $query->when($churchId, fn(Builder $query) => $query->where($column, $churchId));
    }

    protected function applyDateRange(Builder $query, string $column = 'date'): Builder
    {
        $context = $this->getDashboardContext();

        return $query->whereBetween($column, [$context['date_from'], $context['date_to']]);
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
