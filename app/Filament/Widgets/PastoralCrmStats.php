<?php

namespace App\Filament\Widgets;

use App\Models\FollowUp;
use App\Models\Person;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PastoralCrmStats extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralWidgets() ?? false;
    }

    protected function getStats(): array
    {
        $churchId = auth()->user()?->current_church_id;

        $people = Person::query()
            ->when($churchId, fn($query) => $query->where('church_id', $churchId));

        $followUps = FollowUp::query()
            ->when($churchId, fn($query) => $query->where('church_id', $churchId));

        return [
            Stat::make('Personas en CRM', (clone $people)->count()),
            Stat::make('Nuevos creyentes', (clone $people)->where('is_new_believer', true)->count()),
            Stat::make('Seguimientos pendientes', (clone $followUps)->where('status', 'pending')->count()),
            Stat::make('Seguimientos vencidos', (clone $followUps)->overdue()->count()),
        ];
    }
}
