<?php

namespace App\Filament\Widgets\Dashboard\Treasurer;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Movement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalFinanceStats extends StatsOverviewWidget
{
    use InteractsWithDashboardContext;

    public static function canView(): bool
    {
        return auth()->user()?->canViewTreasurerGlobalDashboard() ?? false;
    }

    protected function getStats(): array
    {
        $movements = $this->applyChurchFilter(Movement::query());
        $movements = $this->applyDateRange($movements, 'date');

        $income = (clone $movements)
            ->where('type', 'income')
            ->sum('amount');

        $expense = (clone $movements)
            ->where('type', 'expense')
            ->sum('amount');

        $balance = (float) $income - (float) $expense;

        return [
            Stat::make('Ingresos del periodo', '$' . number_format((float) $income, 2))
                ->description('Ingresos registrados en el rango filtrado'),
            Stat::make('Egresos del periodo', '$' . number_format((float) $expense, 2))
                ->description('Egresos registrados en el rango filtrado'),
            Stat::make('Balance del periodo', '$' . number_format($balance, 2))
                ->description('Ingresos menos egresos'),
        ];
    }
}
