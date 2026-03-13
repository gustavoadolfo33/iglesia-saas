<?php

namespace App\Filament\Widgets\Dashboard\President;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Church;
use Filament\Widgets\ChartWidget;

class AttendanceByChurchChart extends ChartWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Asistencia por iglesia';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralGlobalDashboard() ?? false;
    }

    protected function getData(): array
    {
        $context = $this->getDashboardContext();

        $data = Church::query()
            ->select('churches.name')
            ->selectRaw('COALESCE(SUM(meetings.attendees_count), 0) as total_attendance')
            ->leftJoin('meetings', function ($join) use ($context) {
                $join->on('meetings.church_id', '=', 'churches.id')
                    ->whereBetween('meetings.date', [$context['date_from'], $context['date_to']]);
            })
            ->when($context['church_id'], fn($query, $churchId) => $query->where('churches.id', $churchId))
            ->groupBy('churches.id', 'churches.name')
            ->orderByDesc('total_attendance')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Asistencia',
                    'data' => $data->pluck('total_attendance'),
                    'backgroundColor' => '#d97706',
                ],
            ],
            'labels' => $data->pluck('name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
