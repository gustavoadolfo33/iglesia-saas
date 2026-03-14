<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationTrackLeaderResource\Pages;
use App\Models\FormationTrack;
use App\Models\FormationTrackLeader;
use App\Models\Leader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class FormationTrackLeaderResource extends Resource
{
    protected static ?string $model = FormationTrackLeader::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Formacion';
    protected static ?string $navigationLabel = 'Docentes / facilitadores';
    protected static ?int $navigationSort = 61;
    protected static ?string $modelLabel = 'docente asignado';
    protected static ?string $pluralModelLabel = 'docentes / facilitadores';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewFormationModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageFormationTeachers() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFormationTeachers() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFormationTeachers() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('formation_track_id')
                ->label('Curso')
                ->options(fn() => FormationTrack::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->live()
                ->required(),
            Forms\Components\Select::make('leader_id')
                ->label('Docente / facilitador')
                ->options(function (Get $get) {
                    $track = filled($get('formation_track_id'))
                        ? FormationTrack::query()->find($get('formation_track_id'))
                        : null;

                    return Leader::query()
                        ->when($track?->church_id, fn($query, $churchId) => $query->where('church_id', $churchId))
                        ->with('user')
                        ->get()
                        ->mapWithKeys(fn(Leader $leader) => [$leader->id => $leader->user?->name ?? 'Lider #' . $leader->id])
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->helperText('Para cursos locales solo se muestran docentes de la misma iglesia.')
                ->required(),
            Forms\Components\Select::make('role')
                ->label('Funcion docente')
                ->options([
                    'coordinator' => 'Coordinador',
                    'facilitator' => 'Facilitador',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('track.name')
                    ->label('Curso')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.user.name')
                    ->label('Docente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Funcion')
                    ->formatStateUsing(fn(string $state) => $state === 'coordinator' ? 'Coordinador' : 'Facilitador')
                    ->badge(),
                Tables\Columns\TextColumn::make('track.church.name')
                    ->label('Iglesia')
                    ->placeholder('Global')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('formation_track_id')
                    ->label('Curso')
                    ->relationship('track', 'name'),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Funcion')
                    ->options([
                        'coordinator' => 'Coordinador',
                        'facilitator' => 'Facilitador',
                    ]),
                Tables\Filters\SelectFilter::make('track_church_id')
                    ->label('Iglesia del curso')
                    ->options(fn() => \App\Models\Church::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(fn($query, array $data) => $query->when($data['value'] ?? null, fn($query, $value) => $query->whereHas('track', fn($track) => $track->where('church_id', $value)))),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormationTrackLeaders::route('/'),
            'create' => Pages\CreateFormationTrackLeader::route('/create'),
            'edit' => Pages\EditFormationTrackLeader::route('/{record}/edit'),
        ];
    }

    public static function normalizeTeacherAssignment(array $data, ?FormationTrackLeader $record = null): array
    {
        $track = !empty($data['formation_track_id']) ? FormationTrack::query()->find($data['formation_track_id']) : null;
        $leader = !empty($data['leader_id']) ? Leader::query()->find($data['leader_id']) : null;

        if (!$track || !$leader) {
            return $data;
        }

        if ($track->church_id && (int) $track->church_id !== (int) $leader->church_id) {
            throw ValidationException::withMessages([
                'leader_id' => 'El docente debe pertenecer a la misma iglesia del curso local.',
            ]);
        }

        $exists = FormationTrackLeader::query()
            ->where('formation_track_id', $track->id)
            ->where('leader_id', $leader->id)
            ->when($record, fn($query) => $query->whereKeyNot($record->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'leader_id' => 'Este docente ya está asignado al curso.',
            ]);
        }

        return $data;
    }
}
