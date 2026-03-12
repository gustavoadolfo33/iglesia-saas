<?php

namespace App\Filament\Widgets;

use App\Models\Person;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeopleByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Personas por estado';

    protected function getData(): array
    {
        $churchId = auth()->user()?->current_church_id;

        $data = Person::query()
            ->select('status_id', DB::raw('count(*) as total'))
            ->when($churchId, fn($query) => $query->where('church_id', $churchId))
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
