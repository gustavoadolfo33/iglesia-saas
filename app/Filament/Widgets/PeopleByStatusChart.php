<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Person;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeopleByStatusChart extends ChartWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Personas por estado';

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralGlobalDashboard() ?? false;
    }

    protected function getData(): array
    {
        $context = $this->getDashboardContext();

        $data = Person::query()
            ->select('status_id', DB::raw('count(*) as total'))
            ->when($context['church_id'], fn($query, $churchId) => $query->where('church_id', $churchId))
            ->whereBetween('created_at', [$context['date_from'], $context['date_to']])
            ->with('status')
            ->groupBy('status_id')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Personas',
                'data' => $data->pluck('total'),
            ]],
            'labels' => $data->map(fn($item) => $item->status?->name ?? 'Sin estado'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
