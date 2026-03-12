<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingGroupResource\Pages;
use App\Models\Church;
use App\Models\MeetingGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeetingGroupResource extends Resource
{
    protected static ?string $model = MeetingGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Reuniones';
    protected static ?string $navigationLabel = 'Grupos / Ministerios';
    protected static ?int $navigationSort = 31;
    protected static ?string $modelLabel = 'grupo / ministerio';
    protected static ?string $pluralModelLabel = 'grupos / ministerios';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageMeetingCatalogs() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageMeetingCatalogs() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageMeetingCatalogs() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageMeetingCatalogs() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('church_id')
                ->label('Iglesia')
                ->options(fn() => Church::pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required(fn() => auth()->user()->isGlobalUser())
                ->visible(fn() => auth()->user()->isGlobalUser()),

            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(120),

            Forms\Components\Toggle::make('active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Grupo / Ministerio')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('church.name')
                ->label('Iglesia')
                ->visible(fn() => auth()->user()->isGlobalUser())
                ->sortable(),

            Tables\Columns\IconColumn::make('active')
                ->label('Activo')
                ->boolean(),
        ])
            ->defaultSort('name')
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
            'index' => Pages\ListMeetingGroups::route('/'),
            'create' => Pages\CreateMeetingGroup::route('/create'),
            'edit' => Pages\EditMeetingGroup::route('/{record}/edit'),
        ];
    }
}
