<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceByMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Asistencia por mes';

    public static function canView(): bool
    {
        return auth()->user()?->canViewMeetingsWidgets() ?? false;
    }

    protected function getData(): array
    {
        $data = Meeting::query()
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(attendees_count) as total')
            )
            ->whereYear('date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->map(function ($item) {
            return Carbon::create()->month($item->month)->format('M');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Asistentes',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
