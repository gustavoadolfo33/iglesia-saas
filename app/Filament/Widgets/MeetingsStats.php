<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use App\Support\Dashboard\DashboardContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeetingsStats extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->canViewMeetingsWidgets() ?? false;
    }

    protected function getStats(): array
    {
        $context = DashboardContext::resolve();

        $meetings = Meeting::query()
            ->when($context['church_id'], fn($query, $churchId) => $query->where('church_id', $churchId))
            ->whereBetween('date', [$context['date_from'], $context['date_to']])
            ->get();

        return [
            Stat::make('Reuniones del mes', $meetings->count())
                ->description('Total registradas este mes'),

            Stat::make('Asistentes del mes', $meetings->sum('attendees_count'))
                ->description('Suma total de asistentes'),

            Stat::make('Visitas del mes', $meetings->sum('visitors_count'))
                ->description('Suma total de visitas'),
        ];
    }
}
