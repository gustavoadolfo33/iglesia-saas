<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestMeetings extends BaseWidget
{
    protected static ?string $heading = 'Últimas reuniones registradas';

    protected function getTableQuery(): Builder
    {
        return Meeting::query()
            ->with(['type', 'group', 'church'])
            ->latest('date')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->label('Fecha')
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('type.name')
                ->label('Tipo'),

            Tables\Columns\TextColumn::make('group.name')
                ->label('Grupo'),

            Tables\Columns\TextColumn::make('attendees_count')
                ->label('Asistentes'),

            Tables\Columns\TextColumn::make('visitors_count')
                ->label('Visitas'),

            Tables\Columns\TextColumn::make('church.name')
                ->label('Iglesia')
                ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
        ];
    }
}