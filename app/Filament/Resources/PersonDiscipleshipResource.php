<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonDiscipleshipResource\Pages;
use App\Models\Church;
use App\Models\DiscipleshipTrack;
use App\Models\Leader;
use App\Models\Person;
use App\Models\PersonDiscipleship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonDiscipleshipResource extends Resource
{
    protected static ?string $model = PersonDiscipleship::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Seguimiento pastoral';
    protected static ?string $navigationLabel = 'Proceso de discipulado';
    protected static ?int $navigationSort = 54;
    protected static ?string $modelLabel = 'proceso de discipulado';
    protected static ?string $pluralModelLabel = 'procesos de discipulado';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('church_id')
                ->label('Iglesia')
                ->options(fn() => Church::orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->live()
                ->required(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
            Forms\Components\Select::make('person_id')
                ->label('Persona')
                ->options(function (Get $get) {
                    $user = auth()->user();
                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser()) ? $get('church_id') : $user->current_church_id;
                    if (!$churchId) {
                        return [];
                    }
                    return Person::query()->where('church_id', $churchId)->orderBy('first_name')->get()->mapWithKeys(fn($person) => [$person->id => $person->full_name])->toArray();
                })
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('discipleship_track_id')
                ->label('Ruta')
                ->options(function (Get $get) {
                    $user = auth()->user();
                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser()) ? $get('church_id') : $user->current_church_id;
                    if (!$churchId) {
                        return [];
                    }
                    return DiscipleshipTrack::query()->where('church_id', $churchId)->orderBy('name')->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('leader_id')
                ->label('Mentor')
                ->options(function (Get $get) {
                    $user = auth()->user();
                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser()) ? $get('church_id') : $user->current_church_id;
                    if (!$churchId) {
                        return [];
                    }
                    return Leader::query()->where('church_id', $churchId)->with('user')->get()->pluck('user.name', 'id')->toArray();
                })
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('stage')->label('Etapa')->maxLength(120),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'active' => 'Activo',
                    'paused' => 'Pausado',
                    'completed' => 'Completado',
                    'dropped' => 'Abandonado',
                ])
                ->default('active')
                ->required(),
            Forms\Components\DatePicker::make('started_at')->label('Inicio'),
            Forms\Components\DatePicker::make('completed_at')->label('Finalizacion'),
            Forms\Components\Textarea::make('notes')->label('Notas')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.full_name')->label('Persona')->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('track.name')->label('Ruta')->sortable(),
                Tables\Columns\TextColumn::make('leader.user.name')->label('Mentor')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('started_at')->label('Inicio')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonDiscipleships::route('/'),
            'create' => Pages\CreatePersonDiscipleship::route('/create'),
            'edit' => Pages\EditPersonDiscipleship::route('/{record}/edit'),
        ];
    }
}
