<?php

namespace App\Filament\Widgets\Dashboard\President;

use App\Filament\Widgets\Dashboard\Concerns\InteractsWithDashboardContext;
use App\Models\Church;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowAttendanceChurchesTable extends TableWidget
{
    use InteractsWithDashboardContext;

    protected static ?string $heading = 'Iglesias con menor asistencia';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->canViewExecutiveGlobalDashboard() ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $context = $this->getDashboardContext();

        return Church::query()
            ->select('churches.*')
            ->selectRaw('COUNT(meetings.id) as meetings_count')
            ->selectRaw('COALESCE(SUM(meetings.attendees_count), 0) as total_attendance')
            ->selectRaw('COALESCE(AVG(meetings.attendees_count), 0) as average_attendance')
            ->leftJoin('meetings', function ($join) use ($context) {
                $join->on('meetings.church_id', '=', 'churches.id')
                    ->whereBetween('meetings.date', [$context['date_from'], $context['date_to']]);
            })
            ->when($context['church_id'], fn($query, $churchId) => $query->where('churches.id', $churchId))
            ->groupBy('churches.id')
            ->orderBy('average_attendance')
            ->orderBy('churches.name')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Iglesia'),
            Tables\Columns\TextColumn::make('meetings_count')
                ->label('Reuniones'),
            Tables\Columns\TextColumn::make('average_attendance')
                ->label('Promedio')
                ->formatStateUsing(fn($state) => number_format((float) $state, 1)),
            Tables\Columns\TextColumn::make('total_attendance')
                ->label('Total asistencia'),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
