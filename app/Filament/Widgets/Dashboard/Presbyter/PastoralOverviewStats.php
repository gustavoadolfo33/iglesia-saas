<?php

namespace App\Filament\Widgets\Dashboard\Presbyter;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\FollowUp;
use App\Models\Meeting;
use App\Models\Person;
use App\Models\PersonDiscipleship;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PastoralOverviewStats extends StatsOverviewWidget
{
    use InteractsWithDashboardContext;

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralGlobalDashboard() ?? false;
    }

    protected function getStats(): array
    {
        $context = $this->getDashboardContext();

        $people = $this->applyChurchFilter(Person::query());
        $people = $this->applyDateRange($people, 'created_at');

        $newBelievers = $this->applyChurchFilter(Person::query())
            ->where('is_new_believer', true)
            ->where(function ($query) use ($context) {
                $query->whereBetween('conversion_date', [$context['date_from'], $context['date_to']])
                    ->orWhere(function ($query) use ($context) {
                        $query->whereNull('conversion_date')
                            ->whereBetween('created_at', [$context['date_from'], $context['date_to']]);
                    });
            });

        $followUps = $this->applyChurchFilter(FollowUp::query())
            ->whereIn('status', ['pending', 'overdue'])
            ->where(function ($query) use ($context) {
                $query->whereNull('due_at')
                    ->orWhereBetween('due_at', [$context['date_from'], $context['date_to']]);
            });

        $discipleships = $this->applyChurchFilter(PersonDiscipleship::query())
            ->where(function ($query) use ($context) {
                $query->whereNull('completed_at')
                    ->orWhere('completed_at', '>=', $context['date_from']);
            })
            ->whereDate('started_at', '<=', $context['date_to'])
            ->where('status', '!=', 'completed');

        $meetings = $this->applyChurchFilter(Meeting::query());
        $meetings = $this->applyDateRange($meetings, 'date');

        $averageAttendance = round((float) ((clone $meetings)->avg('attendees_count') ?? 0), 1);

        return [
            Stat::make('Personas registradas', (clone $people)->count())
                ->description('Registradas en el periodo'),
            Stat::make('Nuevos creyentes', (clone $newBelievers)->count())
                ->description('Marcados como nuevos en el periodo'),
            Stat::make('Seguimientos abiertos', (clone $followUps)->count())
                ->description('Pendientes o vencidos'),
            Stat::make('Personas en discipulado', (clone $discipleships)->count())
                ->description('Procesos activos en el periodo'),
            Stat::make('Asistencia promedio', number_format($averageAttendance, 1))
                ->description('Promedio por reunión'),
        ];
    }
}
