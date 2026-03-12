<?php

namespace App\Filament\Widgets;

use App\Models\FollowUp;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class FollowUpsDueToday extends BaseWidget
{
    protected static ?string $heading = 'Seguimientos para hoy';

    public static function canView(): bool
    {
        return auth()->user()?->canViewPastoralWidgets() ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $churchId = auth()->user()?->current_church_id;

        return FollowUp::query()
            ->when($churchId, fn($query) => $query->where('church_id', $churchId))
            ->with(['person', 'leader.user'])
            ->whereDate('due_at', today())
            ->where('status', 'pending')
            ->orderBy('due_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('person.full_name')->label('Persona'),
            Tables\Columns\TextColumn::make('leader.user.name')->label('Lider'),
            Tables\Columns\TextColumn::make('summary')->label('Resumen')->limit(40),
            Tables\Columns\TextColumn::make('due_at')->label('Vence')->dateTime('H:i'),
        ];
    }
}
