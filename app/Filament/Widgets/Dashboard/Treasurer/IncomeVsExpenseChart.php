<?php

namespace App\Filament\Widgets\Dashboard\Treasurer;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Movement;
use Filament\Widgets\ChartWidget;

class IncomeVsExpenseChart extends ChartWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Ingresos vs egresos';

    public static function canView(): bool
    {
        return auth()->user()?->canViewTreasurerGlobalDashboard() ?? false;
    }

    protected function getData(): array
    {
        $movements = $this->applyChurchFilter(Movement::query());
        $movements = $this->applyDateRange($movements, 'date');

        $income = (clone $movements)->where('type', 'income')->sum('amount');
        $expense = (clone $movements)->where('type', 'expense')->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Monto',
                    'data' => [(float) $income, (float) $expense],
                    'backgroundColor' => ['#16a34a', '#dc2626'],
                ],
            ],
            'labels' => ['Ingresos', 'Egresos'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
