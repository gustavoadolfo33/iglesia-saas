<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FollowUpResource\Pages;
use App\Models\Church;
use App\Models\FollowUp;
use App\Models\Leader;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FollowUpResource extends Resource
{
    protected static ?string $model = FollowUp::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Seguimiento pastoral';
    protected static ?string $navigationLabel = 'Seguimientos';
    protected static ?int $navigationSort = 50;
    protected static ?string $modelLabel = 'seguimiento';
    protected static ?string $pluralModelLabel = 'seguimientos';

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
                ->label('Lider responsable')
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
            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'call' => 'Llamada',
                    'visit' => 'Visita',
                    'message' => 'Mensaje',
                    'meeting' => 'Reunion',
                    'prayer' => 'Oracion',
                    'discipleship' => 'Discipulado',
                    'care' => 'Cuidado pastoral',
                ])
                ->default('call')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'pending' => 'Pendiente',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado',
                    'overdue' => 'Vencido',
                ])
                ->default('pending')
                ->required(),
            Forms\Components\Select::make('priority')
                ->label('Prioridad')
                ->options([
                    'low' => 'Baja',
                    'medium' => 'Media',
                    'high' => 'Alta',
                ])
                ->default('medium')
                ->required(),
            Forms\Components\DateTimePicker::make('due_at')->label('Vence'),
            Forms\Components\DateTimePicker::make('completed_at')->label('Completado en'),
            Forms\Components\TextInput::make('summary')->label('Resumen')->required()->maxLength(180),
            Forms\Components\Textarea::make('notes')->label('Notas')->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('person.full_name')->label('Persona')->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('leader.user.name')->label('Lider')->toggleable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('priority')->label('Prioridad')->badge(),
                Tables\Columns\TextColumn::make('due_at')->label('Vence')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'overdue' => 'Vencido',
                    ]),
                Tables\Filters\Filter::make('due_today')
                    ->label('Vence hoy')
                    ->query(fn($query) => $query->whereDate('due_at', today())),
                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidos')
                    ->query(fn($query) => $query->overdue()),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check')
                    ->visible(fn(FollowUp $record) => $record->status !== 'completed')
                    ->action(function (FollowUp $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('reopen')
                    ->label('Reabrir')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(FollowUp $record) => $record->status === 'completed')
                    ->action(function (FollowUp $record) {
                        $record->update([
                            'status' => 'pending',
                            'completed_at' => null,
                        ]);
                    }),
                Tables\Actions\Action::make('mark_overdue')
                    ->label('Marcar vencido')
                    ->icon('heroicon-o-exclamation-circle')
                    ->visible(fn(FollowUp $record) => in_array($record->status, ['pending', 'overdue'], true))
                    ->action(function (FollowUp $record) {
                        $record->update([
                            'status' => 'overdue',
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('due_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFollowUps::route('/'),
            'create' => Pages\CreateFollowUp::route('/create'),
            'edit' => Pages\EditFollowUp::route('/{record}/edit'),
        ];
    }
}
