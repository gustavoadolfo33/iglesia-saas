<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaderResource\Pages;
use App\Models\Church;
use App\Models\Leader;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaderResource extends Resource
{
    protected static ?string $model = Leader::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Seguimiento pastoral';
    protected static ?string $navigationLabel = 'Responsables pastorales';
    protected static ?int $navigationSort = 51;
    protected static ?string $modelLabel = 'responsable pastoral';
    protected static ?string $pluralModelLabel = 'responsables pastorales';

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

            Forms\Components\Select::make('user_id')
                ->label('Usuario')
                ->options(function (Get $get) {
                    $user = auth()->user();
                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser()) ? $get('church_id') : $user->current_church_id;

                    if (!$churchId) {
                        return [];
                    }

                    return User::query()
                        ->whereHas('churches', fn($query) => $query->where('churches.id', $churchId))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('role')
                ->label('Rol pastoral')
                ->maxLength(80),

            Forms\Components\Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Usuario')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')->label('Rol')->sortable(),
                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
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
            'index' => Pages\ListLeaders::route('/'),
            'create' => Pages\CreateLeader::route('/create'),
            'edit' => Pages\EditLeader::route('/{record}/edit'),
        ];
    }
}
