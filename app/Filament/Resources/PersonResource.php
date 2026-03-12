<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Church;
use App\Models\Leader;
use App\Models\Member;
use App\Models\Person;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Personas';
    protected static ?string $navigationLabel = 'Personas';
    protected static ?int $navigationSort = 40;
    protected static ?string $modelLabel = 'persona';
    protected static ?string $pluralModelLabel = 'personas';

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
                    Forms\Components\Select::make('status_id')
                        ->label('Estado')
                        ->options(function (Get $get) {
                            $user = auth()->user();
                            $churchId = ($user->hasRole('super-admin') || $user->isGlobalUser()) ? $get('church_id') : $user->current_church_id;

                            if (!$churchId) {
                                return [];
                            }

                            return Status::query()->where('church_id', $churchId)->orderBy('sort_order')->pluck('name', 'id')->toArray();
                        })
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('assigned_leader_id')
                        ->label('Lider asignado')
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
                    Forms\Components\Select::make('linked_member_id')
                        ->dehydrated(false)
                        ->label('Miembro formal vinculado')
                        ->placeholder('Selecciona un miembro formal disponible')
                        ->options(function (Get $get) {
                            return static::getMemberOptionsForChurch(
                                churchId: static::resolveChurchIdFromForm($get),
                                currentPersonId: null,
                                currentMemberId: $get('linked_member_id'),
                            );
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('Este selector administra el vínculo formal con miembros. La relación principal se guarda en el registro del miembro; la referencia en personas se mantiene solo por compatibilidad temporal.'),
                ])->columns(2),
            Forms\Components\Section::make('Persona')
                ->schema([
                    Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(120),
                    Forms\Components\TextInput::make('last_name')->label('Apellido')->maxLength(120),
                    Forms\Components\TextInput::make('phone')->label('Telefono')->tel()->maxLength(50),
                    Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(150),
                    Forms\Components\TextInput::make('city')->label('Ciudad')->maxLength(100),
                    Forms\Components\DatePicker::make('birth_date')->label('Fecha de nacimiento'),
                    Forms\Components\Textarea::make('address')->label('Direccion')->rows(2),
                ])->columns(2),
            Forms\Components\Section::make('Camino espiritual')
                ->schema([
                    Forms\Components\Select::make('origin_type')
                        ->label('Origen')
                        ->options([
                            'visitor' => 'Visitante',
                            'evangelism' => 'Evangelismo',
                            'referral' => 'Referencia',
                            'event' => 'Evento',
                            'web' => 'Web',
                        ]),
                    Forms\Components\DatePicker::make('visit_date')->label('Fecha de visita'),
                    Forms\Components\DatePicker::make('conversion_date')->label('Fecha de conversion'),
                    Forms\Components\DatePicker::make('baptism_date')->label('Fecha de bautismo'),
                    Forms\Components\Toggle::make('is_new_believer')->label('Nuevo creyente'),
                    Forms\Components\Toggle::make('needs_pastoral_care')->label('Requiere cuidado pastoral'),
                    Forms\Components\Textarea::make('source_notes')->label('Notas de origen')->rows(2),
                    Forms\Components\Textarea::make('notes')->label('Notas generales')->rows(3),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Persona')->searchable(['first_name', 'last_name'])->sortable(['first_name']),
                Tables\Columns\TextColumn::make('status.name')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('assignedLeader.user.name')->label('Lider')->toggleable(),
                Tables\Columns\TextColumn::make('phone')->label('Telefono')->toggleable(),
                Tables\Columns\IconColumn::make('is_new_believer')->label('Nuevo')->boolean(),
                Tables\Columns\IconColumn::make('needs_pastoral_care')->label('Cuidado')->boolean(),
                Tables\Columns\TextColumn::make('church.name')->label('Iglesia')
                    ->visible(fn() => auth()->user()->hasRole('super-admin') || auth()->user()->isGlobalUser()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Estado')
                    ->relationship('status', 'name'),
                Tables\Filters\SelectFilter::make('origin_type')
                    ->label('Origen')
                    ->options([
                        'visitor' => 'Visitante',
                        'evangelism' => 'Evangelismo',
                        'referral' => 'Referencia',
                        'event' => 'Evento',
                        'web' => 'Web',
                    ]),
                Tables\Filters\TernaryFilter::make('is_new_believer')->label('Nuevo creyente'),
                Tables\Filters\TernaryFilter::make('needs_pastoral_care')->label('Cuidado pastoral'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
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

    public static function getMemberOptionsForChurch(?int $churchId, ?int $currentPersonId = null, mixed $currentMemberId = null): array
    {
        if (!$churchId) {
            return [];
        }

        $currentPersonId = $currentPersonId ? (int) $currentPersonId : null;
        $currentMemberId = $currentMemberId ? (int) $currentMemberId : null;

        return Member::query()
            ->where('church_id', $churchId)
            ->where(function (Builder $query) use ($currentPersonId, $currentMemberId) {
                $query->whereNull('person_id');

                if ($currentPersonId) {
                    $query->orWhere('person_id', $currentPersonId);
                }

                if ($currentMemberId) {
                    $query->orWhere('id', $currentMemberId);
                }
            })
            ->orderByRaw('CASE WHEN person_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function (Member $member) use ($currentPersonId) {
                $label = trim($member->first_name . ' ' . $member->last_name);

                if (blank($label)) {
                    $label = 'Miembro #' . $member->id;
                }

                if ($member->person_id && (int) $member->person_id === (int) $currentPersonId) {
                    $label .= ' (vinculado a esta persona)';
                }

                return [$member->id => $label];
            })
            ->toArray();
    }

    public static function syncCanonicalMemberLink(Person $person, ?int $memberId): void
    {
        $memberId = $memberId ?: null;

        Member::query()
            ->where('person_id', $person->id)
            ->when($memberId, fn(Builder $query) => $query->where('id', '!=', $memberId))
            ->update(['person_id' => null]);

        if (!$memberId) {
            return;
        }

        $member = Member::query()->find($memberId);

        if (!$member) {
            return;
        }

        if ((int) $member->church_id !== (int) $person->church_id) {
            return;
        }

        Member::query()
            ->where('id', '!=', $member->id)
            ->where('person_id', $person->id)
            ->update(['person_id' => null]);

        $member->forceFill([
            'person_id' => $person->id,
        ])->save();
    }

    public static function syncLegacyMemberReference(Person $person, ?int $memberId): void
    {
        $memberId = $memberId ?: null;

        if ($memberId) {
            Person::query()
                ->where('id', '!=', $person->id)
                ->where('member_id', $memberId)
                ->update(['member_id' => null]);
        }

        if ((int) $person->member_id !== (int) $memberId) {
            $person->forceFill([
                'member_id' => $memberId,
            ])->save();
        }
    }

    public static function syncMemberLink(Person $person, ?int $memberId): void
    {
        static::syncCanonicalMemberLink($person, $memberId);
        static::syncLegacyMemberReference($person, $memberId);
    }

    public static function getLinkedMemberIdForForm(Person $person): ?int
    {
        return $person->member?->id ?? $person->legacyMember?->id;
    }
}
