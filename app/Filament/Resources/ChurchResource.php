<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchResource\Pages;
use App\Models\Church;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChurchResource extends Resource
{
    protected static ?string $model = Church::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationLabel = 'Iglesias';
    protected static ?int $navigationSort = 11;
    protected static ?string $modelLabel = 'iglesia';
    protected static ?string $pluralModelLabel = 'iglesias';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('iglesias.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('iglesias.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('iglesias.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('iglesias.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(150),

                Forms\Components\Textarea::make('address')
                    ->label('Direccion')
                    ->rows(3)
                    ->maxLength(200),

                TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(100),

                TextInput::make('country')
                    ->label('Pais')
                    ->maxLength(100),

                TextInput::make('phone')
                    ->label('Telefono')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(150),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country')->label('Pais')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Telefono')->toggleable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurches::route('/'),
            'create' => Pages\CreateChurch::route('/create'),
            'edit' => Pages\EditChurch::route('/{record}/edit'),
        ];
    }
}
