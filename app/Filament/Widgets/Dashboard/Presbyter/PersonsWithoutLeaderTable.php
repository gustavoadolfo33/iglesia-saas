<?php

namespace App\Filament\Widgets\Dashboard\Presbyter;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Person;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PersonsWithoutLeaderTable extends TableWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Personas sin líder asignado';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralOperationalGlobalDashboard() ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $query = $this->applyChurchFilter(Person::query()->with(['church', 'status']));

        return $this->applyDateRange($query, 'created_at')
            ->whereNull('assigned_leader_id')
            ->latest('created_at')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('full_name')->label('Persona'),
            Tables\Columns\TextColumn::make('church.name')
                ->label('Iglesia')
                ->visible(fn() => auth()->user()?->canViewPastoralOperationalGlobalDashboard() ?? false),
            Tables\Columns\TextColumn::make('status.name')->label('Estado')->placeholder('—'),
            Tables\Columns\TextColumn::make('created_at')->label('Registro')->date('d/m/Y'),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
