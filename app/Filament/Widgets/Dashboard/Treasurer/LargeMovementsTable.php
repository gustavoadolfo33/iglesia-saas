<?php

namespace App\Filament\Widgets\Dashboard\Treasurer;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Movement;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LargeMovementsTable extends TableWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Movimientos más altos del periodo';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canViewFinancialOperationalGlobalDashboard() ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $query = $this->applyChurchFilter(Movement::query()->with(['church', 'category']));

        return $this->applyDateRange($query, 'date')
            ->orderByDesc('amount')
            ->orderByDesc('date')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->label('Fecha')
                ->date('d/m/Y'),
            Tables\Columns\TextColumn::make('church.name')
                ->label('Iglesia')
                ->visible(fn() => auth()->user()?->canViewFinancialOperationalGlobalDashboard() ?? false),
            Tables\Columns\TextColumn::make('type')
                ->label('Tipo')
                ->formatStateUsing(fn(string $state) => $state === 'income' ? 'Ingreso' : 'Egreso')
                ->badge()
                ->color(fn(string $state) => $state === 'income' ? 'success' : 'danger'),
            Tables\Columns\TextColumn::make('category.name')
                ->label('Categoria')
                ->placeholder('—'),
            Tables\Columns\TextColumn::make('amount')
                ->label('Monto')
                ->money('USD'),
            Tables\Columns\TextColumn::make('description')
                ->label('Descripcion')
                ->limit(40),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
