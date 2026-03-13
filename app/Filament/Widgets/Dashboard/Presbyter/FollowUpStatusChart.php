<?php

namespace App\Filament\Widgets\Dashboard\Presbyter;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\FollowUp;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FollowUpStatusChart extends ChartWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Seguimientos por estado';

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralGlobalDashboard() ?? false;
    }

    protected function getData(): array
    {
        $query = $this->applyChurchFilter(FollowUp::query());
        $query = $this->applyDateRange($query, 'due_at');

        $data = $query
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Seguimientos',
                'data' => $data->pluck('total'),
                'backgroundColor' => ['#f59e0b', '#3b82f6', '#ef4444', '#10b981', '#6b7280'],
            ]],
            'labels' => $data->pluck('status')->map(fn($status) => ucfirst((string) $status)),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
