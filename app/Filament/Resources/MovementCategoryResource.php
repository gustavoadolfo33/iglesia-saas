<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementCategoryResource\Pages;
use App\Models\Church;
use App\Models\MovementCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MovementCategoryResource extends Resource
{
    protected static ?string $model = MovementCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $navigationLabel = 'Categorías financieras';
    protected static ?int $navigationSort = 20;
    protected static ?string $modelLabel = 'categoria financiera';
    protected static ?string $pluralModelLabel = 'categorías financieras';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'income' => 'Ingreso',
                    'expense' => 'Egreso',
                ])
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(120),

            Forms\Components\Toggle::make('active')
                ->label('Activa')
                ->default(true),

            Forms\Components\Select::make('church_id')
                ->label('Iglesia')
                ->options(fn() => Church::pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required(fn() => auth()->user()->isGlobalUser())
                ->visible(fn() => auth()->user()->isGlobalUser()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')
                ->label('Tipo')
                ->badge()
                ->colors([
                    'success' => 'income',
                    'danger' => 'expense',
                ]),

            Tables\Columns\TextColumn::make('name')
                ->label('Categoría')
                ->searchable(),

            Tables\Columns\TextColumn::make('church.name')
                ->label('Iglesia')
                ->visible(fn() => auth()->user()->isGlobalUser()),

            Tables\Columns\IconColumn::make('active')
                ->label('Activa')
                ->boolean(),
        ])
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
            'index' => Pages\ListMovementCategories::route('/'),
            'create' => Pages\CreateMovementCategory::route('/create'),
            'edit' => Pages\EditMovementCategory::route('/{record}/edit'),
        ];
    }
}
