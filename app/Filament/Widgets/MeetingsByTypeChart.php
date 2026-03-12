<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MeetingsByTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Reuniones por tipo (este mes)';

    protected function getData(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $data = Meeting::query()
            ->select('meeting_type_id', DB::raw('count(*) as total'))
            ->whereBetween('date', [$start, $end])
            ->groupBy('meeting_type_id')
            ->with('type')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Reuniones',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->pluck('type.name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}