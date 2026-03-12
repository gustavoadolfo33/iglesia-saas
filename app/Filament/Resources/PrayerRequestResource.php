<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrayerRequestResource\Pages;
use App\Models\Church;
use App\Models\Leader;
use App\Models\Person;
use App\Models\PrayerRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrayerRequestResource extends Resource
{
    protected static ?string $model = PrayerRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Seguimiento pastoral';
    protected static ?string $navigationLabel = 'Pedidos de oración';
    protected static ?int $navigationSort = 55;
    protected static ?string $modelLabel = 'pedido de oración';
    protected static ?string $pluralModelLabel = 'pedidos de oración';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewFollowUpsModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageFollowUpsModule() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFollowUpsModule() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFollowUpsModule() ?? false;
    }

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
            Forms\Components\Select::make('leader_id')
                ->label('Lider')
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
            Forms\Components\TextInput::make('title')->label('Titulo')->required()->maxLength(150),
            Forms\Components\Textarea::make('request')->label('Pedido')->required()->rows(4),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'open' => 'Abierto',
                    'ongoing' => 'En proceso',
                    'answered' => 'Respondido',
                    'closed' => 'Cerrado',
                ])
                ->default('open')
                ->required(),
            Forms\Components\Toggle::make('is_confidential')->label('Confidencial'),
            Forms\Components\DateTimePicker::make('requested_at')->label('Solicitado en')->default(now()),
            Forms\Components\DateTimePicker::make('answered_at')->label('Respondido en'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titulo')->searchable(),
                Tables\Columns\TextColumn::make('person.full_name')->label('Persona')->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\IconColumn::make('is_confidential')->label('Confidencial')->boolean(),
                Tables\Columns\TextColumn::make('requested_at')->label('Solicitado')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('requested_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrayerRequests::route('/'),
            'create' => Pages\CreatePrayerRequest::route('/create'),
            'edit' => Pages\EditPrayerRequest::route('/{record}/edit'),
        ];
    }
}
