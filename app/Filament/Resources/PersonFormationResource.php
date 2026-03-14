<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonFormationResource\Pages;
use App\Filament\Resources\PersonFormationResource\RelationManagers\ProgressRelationManager;
use App\Models\Church;
use App\Models\FormationTrack;
use App\Models\Leader;
use App\Models\Person;
use App\Models\PersonFormation;
use Illuminate\Validation\ValidationException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonFormationResource extends Resource
{
    protected static ?string $model = PersonFormation::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Formacion';
    protected static ?string $navigationLabel = 'Inscripciones';
    protected static ?int $navigationSort = 62;
    protected static ?string $modelLabel = 'alumno inscrito';
    protected static ?string $pluralModelLabel = 'alumnos inscritos';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewFormationEnrollments() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageFormationEnrollments() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFormationEnrollments() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFormationEnrollments() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Inscripcion al curso')
                ->schema([
                    Forms\Components\Select::make('church_id')
                        ->label('Iglesia')
                        ->options(fn() => Church::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->helperText('Se deriva automaticamente desde la persona y la ruta cuando corresponde.')
                        ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
                    Forms\Components\Select::make('person_id')
                        ->label('Alumno')
                        ->options(function (Get $get) {
                            $churchId = static::resolveChurchId($get);

                            if (!$churchId) {
                                return [];
                            }

                            return Person::query()
                                ->where('church_id', $churchId)
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(fn(Person $person) => [$person->id => $person->full_name])
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Solo se muestran personas de la iglesia filtrada o activa.')
                        ->required(),
                    Forms\Components\Select::make('formation_track_id')
                        ->label('Curso')
                        ->options(function (Get $get) {
                            $churchId = static::resolveChurchId($get);

                            return FormationTrack::query()
                                ->when($churchId, fn($query) => $query->where(function ($query) use ($churchId) {
                                    $query->whereNull('church_id')->orWhere('church_id', $churchId);
                                }))
                                ->when(!$churchId, fn($query) => $query->whereNull('church_id'))
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Rutas activas compatibles con la iglesia de la persona.')
                        ->required(),
                    Forms\Components\Select::make('leader_id')
                        ->label('Docente / facilitador')
                        ->options(function (Get $get) {
                            $churchId = static::resolveChurchId($get);

                            if (!$churchId) {
                                return [];
                            }

                            return Leader::query()
                                ->where('church_id', $churchId)
                                ->with('user')
                                ->get()
                                ->mapWithKeys(fn(Leader $leader) => [$leader->id => $leader->user?->name ?? 'Lider #' . $leader->id])
                                ->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Si el track es local, el facilitador debe pertenecer a la misma iglesia.'),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'active' => 'Activa',
                            'paused' => 'Pausada',
                            'completed' => 'Completada',
                            'cancelled' => 'Cancelada',
                        ])
                        ->required()
                        ->default('active'),
                    Forms\Components\DatePicker::make('started_at')
                        ->label('Fecha de inicio')
                        ->required()
                        ->default(now()),
                    Forms\Components\DatePicker::make('completed_at')
                        ->label('Fecha de finalizacion'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Observaciones academicas')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.full_name')
                    ->label('Alumno')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('track.name')
                    ->label('Curso')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.user.name')
                    ->label('Docente')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Finalizacion')
                    ->date()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('formation_track_id')
                    ->label('Ruta')
                    ->relationship('track', 'name'),
                Tables\Filters\SelectFilter::make('leader_id')
                    ->label('Facilitador')
                    ->options(fn() => Leader::query()->with('user')->get()->mapWithKeys(fn(Leader $leader) => [$leader->id => $leader->user?->name ?? 'Lider #' . $leader->id])->toArray()),
                Tables\Filters\SelectFilter::make('church_id')
                    ->label('Iglesia')
                    ->relationship('church', 'name')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
                Tables\Filters\SelectFilter::make('track_category')
                    ->label('Categoria de ruta')
                    ->options([
                        'discipulado' => 'Discipulado',
                        'ministerial' => 'Ministerial',
                        'teologico' => 'Teologico',
                        'familiar' => 'Familiar',
                        'musica' => 'Musica',
                        'artistico' => 'Artistico',
                        'capacitacion' => 'Capacitacion',
                    ])
                    ->query(fn($query, array $data) => $query->when($data['value'] ?? null, fn($query, $value) => $query->whereHas('track', fn($track) => $track->where('category', $value)))),
                Tables\Filters\SelectFilter::make('track_level')
                    ->label('Nivel de ruta')
                    ->options([
                        'basic' => 'Basico',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado',
                        'pastoral' => 'Pastoral',
                    ])
                    ->query(fn($query, array $data) => $query->when($data['value'] ?? null, fn($query, $value) => $query->whereHas('track', fn($track) => $track->where('level', $value)))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProgressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonFormations::route('/'),
            'create' => Pages\CreatePersonFormation::route('/create'),
            'edit' => Pages\EditPersonFormation::route('/{record}/edit'),
        ];
    }

    protected static function resolveChurchId(Get $get): ?int
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if ($user->hasRole('super-admin') || $user->isGlobalUser()) {
            return $get('church_id') ? (int) $get('church_id') : null;
        }

        return $user->current_church_id ? (int) $user->current_church_id : null;
    }

    public static function normalizeFormationData(array $data, ?PersonFormation $record = null): array
    {
        $person = !empty($data['person_id']) ? Person::query()->find($data['person_id']) : null;
        $track = !empty($data['formation_track_id']) ? FormationTrack::query()->find($data['formation_track_id']) : null;
        $leader = !empty($data['leader_id']) ? Leader::query()->find($data['leader_id']) : null;

        if (!$person || !$track) {
            return $data;
        }

        if ($track->scope_type === 'church') {
            if ((int) $track->church_id !== (int) $person->church_id) {
                throw ValidationException::withMessages([
                    'formation_track_id' => 'La ruta de formación no pertenece a la misma iglesia que la persona seleccionada.',
                ]);
            }

            if ($leader && (int) $leader->church_id !== (int) $person->church_id) {
                throw ValidationException::withMessages([
                    'leader_id' => 'El facilitador debe pertenecer a la misma iglesia para rutas locales.',
                ]);
            }
        }

        $duplicate = PersonFormation::query()
            ->where('person_id', $person->id)
            ->where('formation_track_id', $track->id)
            ->whereIn('status', ['active', 'paused'])
            ->when($record, fn($query) => $query->whereKeyNot($record->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'formation_track_id' => 'La persona ya tiene una inscripción activa o pausada en esta ruta.',
            ]);
        }

        $data['church_id'] = $person->church_id;

        if (($data['status'] ?? null) !== 'completed') {
            $data['completed_at'] = null;
        }

        return $data;
    }
}
