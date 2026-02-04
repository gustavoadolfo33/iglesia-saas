<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Church;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;

use Filament\Tables;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->required(fn(string $operation) => $operation === 'create')
                ->helperText('En edición: deja vacío para no cambiar.'),

            // Roles (Spatie) - virtual field (se sincroniza en Pages)
            CheckboxList::make('roles')
                ->label('Roles')
                ->options(Role::query()->pluck('name', 'name')->toArray())
                ->columns(2)
                ->live()
                ->required()
                ->helperText('Roles internos pueden entrar al panel /admin.'),

            // Asignar iglesias (many-to-many)
            CheckboxList::make('churches')
                ->label('Iglesias asignadas')
                ->relationship('churches', 'name')
                ->columns(2)
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $current = $get('current_church_id');

                    if ($current && is_array($state) && !in_array((int) $current, $state, true)) {
                        $set('current_church_id', null);
                    }
                })
                ->required(function (callable $get) {
                    $roles = $get('roles') ?? [];
                    return in_array('pastor', $roles, true) || in_array('contador', $roles, true);
                }),

            // Iglesia activa (solo entre iglesias asignadas)
            Select::make('current_church_id')
                ->label('Iglesia activa')
                ->searchable()
                ->preload()
                ->nullable()
                ->live()
                ->options(function (callable $get) {
                    $churchIds = $get('churches') ?? [];

                    if (empty($churchIds)) {
                        return [];
                    }

                    return Church::query()
                        ->whereIn('id', $churchIds)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->required(function (callable $get) {
                    $roles = $get('roles') ?? [];
                    return in_array('pastor', $roles, true) || in_array('contador', $roles, true);
                })
                ->helperText('Solo puedes elegir entre las iglesias asignadas.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge(),

                Tables\Columns\TextColumn::make('currentChurch.name')
                    ->label('Iglesia activa')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')->label('Creado')->dateTime()->sortable(),
            ])
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}