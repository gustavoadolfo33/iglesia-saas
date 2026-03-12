<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages;
use App\Models\Church;
use App\Models\Meeting;
use App\Models\MeetingGroup;
use App\Models\MeetingType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Reuniones';
    protected static ?string $navigationLabel = 'Reuniones';
    protected static ?int $navigationSort = 30;
    protected static ?string $modelLabel = 'reunion';
    protected static ?string $pluralModelLabel = 'reuniones';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewMeetingsModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageMeetingsModule() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageMeetingsModule() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageMeetingsModule() ?? false;
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

            Forms\Components\DatePicker::make('date')
                ->label('Fecha')
                ->required(),

            Forms\Components\Select::make('meeting_type_id')
                ->label('Tipo de reunión')
                ->options(function (Get $get) {
                    $user = auth()->user();

                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser())
                        ? $get('church_id')
                        : $user->current_church_id;

                    if (!$churchId) {
                        return [];
                    }

                    return MeetingType::query()
                        ->where('church_id', $churchId)
                        ->where('active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required()
                ->live(),

            Forms\Components\Select::make('meeting_group_id')
                ->label('Grupo / Ministerio')
                ->options(function (Get $get) {
                    $user = auth()->user();

                    $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser())
                        ? $get('church_id')
                        : $user->current_church_id;

                    if (!$churchId) {
                        return [];
                    }

                    return MeetingGroup::query()
                        ->where('church_id', $churchId)
                        ->where('active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\TimePicker::make('start_time')
                ->label('Hora inicio')
                ->seconds(false)
                ->nullable(),

            Forms\Components\TimePicker::make('end_time')
                ->label('Hora fin')
                ->seconds(false)
                ->nullable(),

            Forms\Components\TextInput::make('topic')
                ->label('Tema')
                ->maxLength(200)
                ->nullable(),

            Forms\Components\TextInput::make('bible_reference')
                ->label('Base bíblica')
                ->maxLength(200)
                ->nullable(),

            Forms\Components\TextInput::make('leader_name')
                ->label('Responsable')
                ->maxLength(120)
                ->nullable(),

            Forms\Components\TextInput::make('guest')
                ->label('Invitado')
                ->maxLength(120)
                ->nullable(),

            Forms\Components\TextInput::make('attendees_count')
                ->label('Asistentes')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('visitors_count')
                ->label('Visitas')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'planned' => 'Planificada',
                    'done' => 'Realizada',
                    'cancelled' => 'Cancelada',
                ])
                ->default('done')
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('Comentarios / Observaciones')
                ->rows(3)
                ->maxLength(1000)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type.name')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo / Ministerio')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('Tema')
                    ->limit(35)
                    ->searchable(),

                Tables\Columns\TextColumn::make('attendees_count')
                    ->label('Asistentes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('visitors_count')
                    ->label('Visitas')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'planned',
                        'success' => 'done',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meeting_type_id')
                    ->label('Tipo')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user->hasRole('super-admin') || $user->isGlobalUser()) {
                            return MeetingType::orderBy('name')->pluck('name', 'id')->toArray();
                        }

                        return MeetingType::where('church_id', $user->current_church_id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('meeting_group_id')
                    ->label('Grupo')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user->hasRole('super-admin') || $user->isGlobalUser()) {
                            return MeetingGroup::orderBy('name')->pluck('name', 'id')->toArray();
                        }

                        return MeetingGroup::where('church_id', $user->current_church_id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'planned' => 'Planificada',
                        'done' => 'Realizada',
                        'cancelled' => 'Cancelada',
                    ]),

                Tables\Filters\SelectFilter::make('church_id')
                    ->label('Iglesia')
                    ->options(fn() => Church::orderBy('name')->pluck('name', 'id')->toArray())
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
