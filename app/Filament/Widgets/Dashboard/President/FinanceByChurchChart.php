<?php

namespace App\Filament\Widgets\Dashboard\President;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Church;
use Filament\Widgets\ChartWidget;

class FinanceByChurchChart extends ChartWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Finanzas por iglesia';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canViewTreasurerGlobalDashboard() ?? false;
    }

    protected function getData(): array
    {
        $context = $this->getDashboardContext();

        $data = Church::query()
            ->select('churches.name')
            ->selectRaw("COALESCE(SUM(CASE WHEN movements.type = 'income' THEN movements.amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN movements.type = 'expense' THEN movements.amount ELSE 0 END), 0) as expense_total")
            ->leftJoin('movements', function ($join) use ($context) {
                $join->on('movements.church_id', '=', 'churches.id')
                    ->whereBetween('movements.date', [$context['date_from'], $context['date_to']]);
            })
            ->when($context['church_id'], fn($query, $churchId) => $query->where('churches.id', $churchId))
            ->groupBy('churches.id', 'churches.name')
            ->orderBy('churches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data->pluck('income_total'),
                    'backgroundColor' => '#16a34a',
                ],
                [
                    'label' => 'Egresos',
                    'data' => $data->pluck('expense_total'),
                    'backgroundColor' => '#dc2626',
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
