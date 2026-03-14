<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationTrackResource\Pages;
use App\Filament\Resources\FormationTrackResource\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\FormationTrackResource\RelationManagers\TrackLeadersRelationManager;
use App\Models\Church;
use App\Models\FormationTrack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FormationTrackResource extends Resource
{
    protected static ?string $model = FormationTrack::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Formacion';
    protected static ?string $navigationLabel = 'Cursos';
    protected static ?int $navigationSort = 60;
    protected static ?string $modelLabel = 'curso';
    protected static ?string $pluralModelLabel = 'cursos';

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
        return auth()->user()?->canManageFormationCourses() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFormationCourses() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFormationCourses() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Curso')
                ->schema([
                    Forms\Components\Select::make('church_id')
                        ->label('Iglesia')
                        ->options(fn() => Church::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->helperText('Obligatoria solo para rutas con alcance de iglesia.')
                        ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                        ->required(fn(Get $get) => $get('scope_type') === 'church' && (auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())),
                    Forms\Components\Select::make('scope_type')
                        ->label('Alcance del curso')
                        ->options([
                            'church' => 'Iglesia',
                            'city' => 'Ciudad',
                            'organization' => 'Organizacion',
                        ])
                        ->required()
                        ->default('church')
                        ->live(),
                    Forms\Components\TextInput::make('scope_label')
                        ->label('Etiqueta de alcance')
                        ->maxLength(150)
                        ->placeholder('Ej. La Paz, Red nacional')
                        ->helperText('Usa este campo para identificar ciudad, red u organización cuando no sea un curso local de iglesia.'),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Forms\Set $set, Get $get): void {
                            if (filled($get('slug'))) {
                                return;
                            }

                            $set('slug', str($state)->slug()->toString());
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Textarea::make('description')
                        ->label('Descripcion del curso')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Forms\Components\Section::make('Clasificacion academica')
                ->schema([
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'discipulado' => 'Discipulado',
                            'ministerial' => 'Ministerial',
                            'teologico' => 'Teologico',
                            'familiar' => 'Familiar',
                            'musica' => 'Musica',
                            'artistico' => 'Artistico',
                            'capacitacion' => 'Capacitacion',
                        ])
                        ->required(),
                    Forms\Components\Select::make('level')
                        ->label('Nivel')
                        ->options([
                            'basic' => 'Basico',
                            'intermediate' => 'Intermedio',
                            'advanced' => 'Avanzado',
                            'pastoral' => 'Pastoral',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('affects_pastoral_flow')
                        ->label('Afecta flujo pastoral')
                        ->default(false),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true),
                ])
                ->columns(2),
            Forms\Components\Section::make('Costo del curso')
                ->schema([
                    Forms\Components\Toggle::make('is_paid')
                        ->label('Es de paga')
                        ->default(false)
                        ->live(),
                    Forms\Components\TextInput::make('price')
                        ->label('Precio')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->required(fn(Get $get) => (bool) $get('is_paid'))
                        ->visible(fn(Get $get) => (bool) $get('is_paid')),
                    Forms\Components\TextInput::make('currency')
                        ->label('Moneda')
                        ->maxLength(10)
                        ->required(fn(Get $get) => (bool) $get('is_paid'))
                        ->visible(fn(Get $get) => (bool) $get('is_paid')),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(['name', 'slug', 'description'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('track_leaders_count')
                    ->label('Docentes')
                    ->counts('trackLeaders')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Nivel')
                    ->badge(),
                Tables\Columns\TextColumn::make('scope_type')
                    ->label('Alcance')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'church' => 'Iglesia',
                        'city' => 'Ciudad',
                        'organization' => 'Organizacion',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->placeholder('Global')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('is_paid')
                    ->label('Costo')
                    ->formatStateUsing(fn(bool $state) => $state ? 'De paga' : 'Gratuita')
                    ->badge()
                    ->color(fn(bool $state) => $state ? 'warning' : 'success'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'discipulado' => 'Discipulado',
                        'ministerial' => 'Ministerial',
                        'teologico' => 'Teologico',
                        'familiar' => 'Familiar',
                        'musica' => 'Musica',
                        'artistico' => 'Artistico',
                        'capacitacion' => 'Capacitacion',
                    ]),
                Tables\Filters\SelectFilter::make('level')
                    ->label('Nivel')
                    ->options([
                        'basic' => 'Basico',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado',
                        'pastoral' => 'Pastoral',
                    ]),
                Tables\Filters\SelectFilter::make('scope_type')
                    ->label('Alcance')
                    ->options([
                        'church' => 'Iglesia',
                        'city' => 'Ciudad',
                        'organization' => 'Organizacion',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa'),
                Tables\Filters\TernaryFilter::make('affects_pastoral_flow')
                    ->label('Afecta flujo pastoral'),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Es de paga'),
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
            LessonsRelationManager::class,
            TrackLeadersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormationTracks::route('/'),
            'create' => Pages\CreateFormationTrack::route('/create'),
            'edit' => Pages\EditFormationTrack::route('/{record}/edit'),
        ];
    }
}
