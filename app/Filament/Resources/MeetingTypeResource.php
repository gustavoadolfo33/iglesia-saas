<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingTypeResource\Pages;
use App\Models\Church;
use App\Models\MeetingType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MeetingTypeResource extends Resource
{
    protected static ?string $model = MeetingType::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Reuniones';
    protected static ?string $navigationLabel = 'Tipos de reunión';

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
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    if (filled($state)) {
                        $set('slug', Str::slug($state));
                    }
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(150)
                ->helperText('Se genera automáticamente, pero puedes ajustarlo.'),

            Forms\Components\Toggle::make('active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tipo')
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
            'index' => Pages\ListMeetingTypes::route('/'),
            'create' => Pages\CreateMeetingType::route('/create'),
            'edit' => Pages\EditMeetingType::route('/{record}/edit'),
        ];
    }
}