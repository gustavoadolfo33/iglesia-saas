<?php

namespace App\Filament\Widgets\Dashboard\President;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Church;
use App\Models\Meeting;
use App\Models\Movement;
use App\Models\Person;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PresidentExecutiveStats extends StatsOverviewWidget
{
    use InteractsWithDashboardContext;

    protected function getStats(): array
    {
        $context = $this->getDashboardContext();

        $activeChurches = Church::query()
            ->where('status', 'active')
            ->when($context['church_id'], fn($query, $churchId) => $query->whereKey($churchId))
            ->count();

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
            })
            ->count();

        $meetings = $this->applyChurchFilter(Meeting::query());
        $meetings = $this->applyDateRange($meetings, 'date');

        $averageAttendance = round((float) ((clone $meetings)->avg('attendees_count') ?? 0), 1);

        $offerings = $this->applyChurchFilter(Movement::query());
        $offerings = $this->applyDateRange($offerings, 'date');
        $offerings = (clone $offerings)
            ->where('type', 'income')
            ->sum('amount');

        return [
            Stat::make('Iglesias activas', $activeChurches)
                ->description($context['church_id'] ? 'Iglesia filtrada' : 'Vista global'),
            Stat::make('Personas registradas', (clone $people)->count())
                ->description('Registradas en el periodo'),
            Stat::make('Nuevos creyentes', $newBelievers)
                ->description('Conversiones o registros marcados como nuevos'),
            Stat::make('Asistencia promedio', number_format($averageAttendance, 1))
                ->description('Promedio por reunión en el periodo'),
            Stat::make('Ofrendas del periodo', '$' . number_format((float) $offerings, 2))
                ->description('Ingresos registrados en el periodo'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->canViewExecutiveGlobalDashboard() ?? false;
    }
}
