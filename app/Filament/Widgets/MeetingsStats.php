<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MeetingsStats extends BaseWidget
{
    protected function getStats(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $meetings = Meeting::query()
            ->whereBetween('date', [$start, $end])
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