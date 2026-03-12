<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Church;
use App\Models\Household;
use App\Models\Member;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Personas';
    protected static ?string $navigationLabel = 'Miembros';
    protected static ?int $navigationSort = 41;
    protected static ?string $modelLabel = 'miembro';
    protected static ?string $pluralModelLabel = 'miembros';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewPeopleModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManagePeopleModule() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManagePeopleModule() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManagePeopleModule() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Contexto')
                ->schema([
                    Forms\Components\Select::make('church_id')
                        ->label('Iglesia')
                        ->options(fn() => Church::orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                        ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
                    Forms\Components\Select::make('household_id')
                        ->label('Hogar')
                        ->options(function (Get $get) {
                            $churchId = static::resolveChurchIdFromForm($get);

                            if (!$churchId) {
                                return [];
                            }

                            return Household::query()
                                ->where('church_id', $churchId)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('person_id')
                        ->label('Persona vinculada')
                        ->options(function (Get $get) {
                            return static::getPersonOptionsForChurch(
                                churchId: static::resolveChurchIdFromForm($get),
                                currentMemberId: null,
                                currentPersonId: $get('person_id'),
                            );
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Opcional. Mantiene compatibilidad temporal con persons.member_id y la nueva relacion members.person_id.'),
                ])->columns(2),
            Forms\Components\Section::make('Miembro')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Apellido')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(150),
                    Forms\Components\TextInput::make('phone')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(50),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Miembro')
                    ->getStateUsing(fn(Member $record) => trim($record->first_name . ' ' . $record->last_name))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('person.full_name')
                    ->label('Persona vinculada')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('household.name')
                    ->label('Hogar')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('church.name')
                    ->label('Iglesia')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser())
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('first_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    public static function resolveChurchIdFromForm(Get $get): ?int
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser())
            ? $get('church_id')
            : $user->current_church_id;

        return $churchId ? (int) $churchId : null;
    }

    public static function getPersonOptionsForChurch(?int $churchId, ?int $currentMemberId = null, mixed $currentPersonId = null): array
    {
        if (!$churchId) {
            return [];
        }

        $currentMemberId = $currentMemberId ? (int) $currentMemberId : null;
        $currentPersonId = $currentPersonId ? (int) $currentPersonId : null;

        return Person::query()
            ->where('church_id', $churchId)
            ->where(function (Builder $query) use ($currentMemberId, $currentPersonId) {
                $query->whereDoesntHave('member');

                if ($currentPersonId) {
                    $query->orWhere('id', $currentPersonId);
                }

                if ($currentMemberId) {
                    $query->orWhere('member_id', $currentMemberId);
                }
            })
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn(Person $person) => [$person->id => $person->full_name])
            ->toArray();
    }

    public static function syncPersonLink(Member $member, ?int $personId): void
    {
        $personId = $personId ?: null;

        if ($personId) {
            Member::query()
                ->where('person_id', $personId)
                ->where('id', '!=', $member->id)
                ->update(['person_id' => null]);
        }

        Person::query()
            ->where('member_id', $member->id)
            ->when($personId, fn(Builder $query) => $query->where('id', '!=', $personId))
            ->update(['member_id' => null]);

        if ($member->person_id !== $personId) {
            $member->forceFill([
                'person_id' => $personId,
            ])->save();
        }

        if (!$personId) {
            return;
        }

        $person = Person::query()->find($personId);

        if (!$person) {
            return;
        }

        if ((int) $person->member_id !== (int) $member->id) {
            $person->forceFill([
                'member_id' => $member->id,
            ])->save();
        }
    }
}
