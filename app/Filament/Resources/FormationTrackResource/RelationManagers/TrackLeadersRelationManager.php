<?php

namespace App\Filament\Resources\FormationTrackResource\RelationManagers;

use App\Models\Leader;
use App\Models\FormationTrackLeader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class TrackLeadersRelationManager extends RelationManager
{
    protected static string $relationship = 'trackLeaders';
    protected static ?string $title = 'Docentes / facilitadores';
    protected static ?string $modelLabel = 'docente asignado';
    protected static ?string $pluralModelLabel = 'docentes asignados';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->canViewFormationModule() ?? false;
    }

    public function isReadOnly(): bool
    {
        return !(auth()->user()?->canManageFormationTeachers() ?? false);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('leader_id')
                ->label('Docente / facilitador')
                ->options(function () {
                    $track = $this->getOwnerRecord();

                    return Leader::query()
                        ->when($track->church_id, fn($query, $churchId) => $query->where('church_id', $churchId))
                        ->with('user')
                        ->get()
                        ->mapWithKeys(fn(Leader $leader) => [$leader->id => $leader->user?->name ?? 'Lider #' . $leader->id])
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->helperText('Solo se muestran lideres compatibles con el alcance de la ruta.')
                ->required(),
            Forms\Components\Select::make('role')
                ->label('Rol')
                ->options([
                    'coordinator' => 'Coordinador',
                    'facilitator' => 'Facilitador',
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leader.user.name')
                    ->label('Docente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->formatStateUsing(fn(string $state) => $state === 'coordinator' ? 'Coordinador' : 'Facilitador')
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $track = $this->getOwnerRecord();
                        $leader = Leader::query()->find($data['leader_id']);

                        if ($track->church_id && $leader && (int) $leader->church_id !== (int) $track->church_id) {
                            throw ValidationException::withMessages([
                                'leader_id' => 'El líder debe pertenecer a la misma iglesia de la ruta local.',
                            ]);
                        }

                        $exists = FormationTrackLeader::query()
                            ->where('formation_track_id', $track->id)
                            ->where('leader_id', $data['leader_id'])
                            ->exists();

                        if ($exists) {
                            throw ValidationException::withMessages([
                                'leader_id' => 'Ese líder ya fue asignado a esta ruta.',
                            ]);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $track = $this->getOwnerRecord();
                        $leader = Leader::query()->find($data['leader_id']);
                        $record = $this->getMountedTableActionRecord();

                        if ($track->church_id && $leader && (int) $leader->church_id !== (int) $track->church_id) {
                            throw ValidationException::withMessages([
                                'leader_id' => 'El líder debe pertenecer a la misma iglesia de la ruta local.',
                            ]);
                        }

                        $exists = FormationTrackLeader::query()
                            ->where('formation_track_id', $track->id)
                            ->where('leader_id', $data['leader_id'])
                            ->whereKeyNot($record->getKey())
                            ->exists();

                        if ($exists) {
                            throw ValidationException::withMessages([
                                'leader_id' => 'Ese líder ya fue asignado a esta ruta.',
                            ]);
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
