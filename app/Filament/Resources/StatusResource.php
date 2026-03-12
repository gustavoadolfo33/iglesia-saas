<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Church;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Seguimiento pastoral';
    protected static ?string $navigationLabel = 'Estados pastorales';
    protected static ?int $navigationSort = 52;
    protected static ?string $modelLabel = 'estado pastoral';
    protected static ?string $pluralModelLabel = 'estados pastorales';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewPastoralSettings() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManagePastoralSettings() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManagePastoralSettings() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManagePastoralSettings() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('church_id')
                ->label('Iglesia')
                ->options(fn() => Church::orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),

            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(120),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->maxLength(140),

            Forms\Components\Select::make('category')
                ->label('Categoria')
                ->options([
                    'visitor' => 'Visitante',
                    'evangelism' => 'Evangelismo',
                    'new_believer' => 'Nuevo creyente',
                    'discipleship' => 'Discipulado',
                    'care' => 'Cuidado pastoral',
                    'inactive' => 'Inactivo',
                ])
                ->required(),

            Forms\Components\TextInput::make('color')
                ->label('Color')
                ->maxLength(30),

            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\Toggle::make('is_default')
                ->label('Por defecto'),

            Forms\Components\Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Categoria')->badge(),
                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
                Tables\Columns\IconColumn::make('is_default')->label('Default')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('sort_order')
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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
