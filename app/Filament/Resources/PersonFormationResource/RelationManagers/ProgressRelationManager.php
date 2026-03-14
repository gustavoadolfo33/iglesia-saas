<?php

namespace App\Filament\Resources\PersonFormationResource\RelationManagers;

use App\Models\FormationLesson;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class ProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'progress';
    protected static ?string $title = 'Progreso academico';
    protected static ?string $modelLabel = 'avance';
    protected static ?string $pluralModelLabel = 'avance academico';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->canViewFormationEnrollments() ?? false;
    }

    public function isReadOnly(): bool
    {
        return !(auth()->user()?->canManageFormationProgress() ?? false);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('formation_lesson_id')
                ->label('Clase / leccion')
                ->options(function () {
                    $personFormation = $this->getOwnerRecord();

                    return FormationLesson::query()
                        ->where('formation_track_id', $personFormation->formation_track_id)
                        ->orderBy('sort_order')
                        ->pluck('title', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->unique(ignoreRecord: true, modifyRuleUsing: fn(Unique $rule) => $rule->where('person_formation_id', $this->getOwnerRecord()->id))
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'pending' => 'Pendiente',
                    'in_progress' => 'En progreso',
                    'completed' => 'Completada',
                    'skipped' => 'Omitida',
                ])
                ->required()
                ->default('pending'),
            Forms\Components\DateTimePicker::make('completed_at')
                ->label('Completado en')
                ->helperText('Si marcas la lección como completada, se registrará esta fecha.'),
            Forms\Components\Select::make('reviewed_by')
                ->label('Revisado por')
                ->options(fn() => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('notes')
                ->label('Notas de seguimiento')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Clase')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completado')
                    ->dateTime()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Revisado por')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['status'] ?? null) === 'completed' && empty($data['completed_at'])) {
                            $data['completed_at'] = now();
                        }

                        if (($data['status'] ?? null) !== 'completed') {
                            $data['completed_at'] = null;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['status'] ?? null) === 'completed' && empty($data['completed_at'])) {
                            $data['completed_at'] = now();
                        }

                        if (($data['status'] ?? null) !== 'completed') {
                            $data['completed_at'] = null;
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
