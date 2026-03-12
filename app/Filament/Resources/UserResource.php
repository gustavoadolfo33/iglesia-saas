<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Church;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use Filament\Tables;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    public const LOCAL_ADDITIONAL_PERMISSION_LABELS = [
        'ver_dashboard_local' => 'Ver dashboard local',
        'ver_finanzas' => 'Ver finanzas',
        'registrar_finanzas' => 'Registrar finanzas',
        'ver_reuniones' => 'Ver reuniones',
        'registrar_reuniones' => 'Registrar reuniones',
        'ver_personas' => 'Ver personas',
        'registrar_personas' => 'Registrar personas',
        'ver_seguimientos' => 'Ver seguimientos',
        'registrar_seguimientos' => 'Registrar seguimientos',
        'ver_discipulado' => 'Ver discipulado',
        'registrar_discipulado' => 'Registrar discipulado',
        'exportar_reportes' => 'Exportar reportes',
    ];

    public const LOCAL_ROLE_ADDITIONAL_PERMISSIONS = [
        'contador-local' => [
            'ver_dashboard_local',
            'exportar_reportes',
            'ver_finanzas',
            'registrar_finanzas',
        ],
        'encargado-reuniones' => [
            'ver_dashboard_local',
            'ver_reuniones',
            'registrar_reuniones',
            'exportar_reportes',
        ],
        'encargado-seguimiento' => [
            'ver_dashboard_local',
            'ver_personas',
            'registrar_personas',
            'ver_seguimientos',
            'registrar_seguimientos',
            'ver_discipulado',
            'registrar_discipulado',
        ],
        'secretario-registro' => [
            'ver_dashboard_local',
            'ver_personas',
            'registrar_personas',
            'ver_reuniones',
            'registrar_reuniones',
            'exportar_reportes',
        ],
        'discipulador' => [
            'ver_personas',
            'ver_discipulado',
            'registrar_discipulado',
            'ver_seguimientos',
        ],
    ];

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'usuario';
    protected static ?string $pluralModelLabel = 'usuarios';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageUsers() ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (!$user || !$user->canManageUsers()) {
            return false;
        }

        if (!$user->hasRole('pastor')) {
            return true;
        }

        return $record->churches()->where('churches.id', $user->current_church_id)->exists()
            && $record->roles()->whereIn('roles.name', User::PASTOR_ASSIGNABLE_LOCAL_ROLES)->exists()
            && !$record->roles()->whereIn('roles.name', User::GLOBAL_ROLES)->exists()
            && !$record->hasRole('pastor');
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->hasRole('pastor') && $user->current_church_id) {
            $query
                ->whereHas('churches', fn(Builder $churches) => $churches->where('churches.id', $user->current_church_id))
                ->whereHas('roles', fn(Builder $roles) => $roles->whereIn('roles.name', User::PASTOR_ASSIGNABLE_LOCAL_ROLES))
                ->whereDoesntHave('roles', fn(Builder $roles) => $roles->whereIn('roles.name', User::GLOBAL_ROLES))
                ->whereDoesntHave('roles', fn(Builder $roles) => $roles->where('roles.name', 'pastor'));
        }

        return $query;
    }

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

            CheckboxList::make('roles')
                ->label('Roles')
                ->options(static::getAssignableRoleOptions())
                ->columns(2)
                ->live()
                ->required(fn() => !static::isPastorManager())
                ->visible(fn() => !static::isPastorManager())
                ->dehydrated(fn() => !static::isPastorManager())
                ->helperText('Solo se muestran perfiles que el usuario autenticado puede asignar.'),

            Select::make('base_role')
                ->label('Perfil base local')
                ->options(static::getPastorBaseRoleOptions())
                ->searchable()
                ->preload()
                ->live()
                ->visible(fn() => static::isPastorManager())
                ->required(fn() => static::isPastorManager())
                ->helperText('El perfil base define el alcance local principal del usuario.'),

            Section::make('Accesos adicionales')
                ->schema([
                    CheckboxList::make('extra_permissions')
                        ->label('Accesos adicionales')
                        ->options(fn(Get $get) => static::getAdditionalPermissionOptionsForBaseRole($get('base_role')))
                        ->columns(2)
                        ->visible(fn(Get $get) => static::isPastorManager() && filled($get('base_role')))
                        ->helperText('Solo puedes activar accesos compatibles con el perfil base seleccionado.'),
                ])
                ->visible(fn() => static::isPastorManager())
                ->collapsible(),

            CheckboxList::make('churches')
                ->label('Iglesias asignadas')
                ->relationship(
                    name: 'churches',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn(Builder $query) => $query->whereIn('churches.id', static::allowedChurchIdsForCurrentUser()),
                )
                ->columns(2)
                ->live()
                ->visible(fn() => !static::isPastorManager())
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $current = $get('current_church_id');

                    if ($current && is_array($state) && !in_array((int) $current, $state, true)) {
                        $set('current_church_id', null);
                    }
                })
                ->required(function (callable $get) {
                    return static::selectedRolesNeedChurch($get('roles') ?? []);
                })
                ->helperText('Los perfiles locales deben quedar vinculados a una iglesia.'),

            Select::make('current_church_id')
                ->label('Iglesia activa')
                ->searchable()
                ->preload()
                ->nullable()
                ->live()
                ->visible(fn() => !static::isPastorManager())
                ->options(function (callable $get) {
                    $churchIds = $get('churches') ?? [];

                    if (empty($churchIds)) {
                        return [];
                    }

                    return Church::query()
                        ->whereIn('churches.id', $churchIds)
                        ->pluck('churches.name', 'churches.id')
                        ->toArray();
                })
                ->required(function (callable $get) {
                    return static::selectedRolesNeedChurch($get('roles') ?? []);
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

    public static function getAssignableRoleOptions(): array
    {
        $allowedRoles = User::assignableRoleNamesFor(auth()->user());

        if (empty($allowedRoles)) {
            return [];
        }

        return Role::query()
            ->whereIn('name', $allowedRoles)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->mapWithKeys(fn(string $name) => [$name => static::getRoleLabel($name)])
            ->toArray();
    }

    public static function allowedChurchIdsForCurrentUser(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        if ($user->hasRole('pastor')) {
            return $user->current_church_id ? [$user->current_church_id] : [];
        }

        return Church::query()
            ->orderBy('churches.name')
            ->pluck('churches.id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    public static function normalizeManagementData(array $data): array
    {
        $roles = static::isPastorManager()
            ? static::sanitizePastorBaseRole($data['base_role'] ?? null)
            : static::sanitizeRoles($data['roles'] ?? []);
        $churchIds = static::sanitizeChurchIds($data['churches'] ?? []);
        $extraPermissions = static::sanitizeAdditionalPermissions(
            $data['base_role'] ?? null,
            $data['extra_permissions'] ?? []
        );

        if (static::selectedRolesNeedChurch($roles) && auth()->user()?->hasRole('pastor') && auth()->user()?->current_church_id) {
            $churchIds = [auth()->user()->current_church_id];
        }

        $currentChurchId = isset($data['current_church_id']) ? (int) $data['current_church_id'] : null;

        if (!in_array($currentChurchId, $churchIds, true)) {
            $currentChurchId = static::selectedRolesNeedChurch($roles) ? ($churchIds[0] ?? null) : null;
        }

        $data['roles'] = $roles;
        $data['base_role'] = $roles[0] ?? null;
        $data['extra_permissions'] = $extraPermissions;
        $data['churches'] = $churchIds;
        $data['current_church_id'] = $currentChurchId;

        return $data;
    }

    public static function syncChurchAssignments(User $user, array $data): void
    {
        $normalized = static::normalizeManagementData($data);
        $churchIds = $normalized['churches'] ?? [];
        $roles = $normalized['roles'] ?? [];

        if (static::selectedRolesNeedChurch($roles) && empty($churchIds)) {
            $churchIds = static::allowedChurchIdsForCurrentUser();

            if (auth()->user()?->hasRole('pastor') && auth()->user()?->current_church_id) {
                $churchIds = [auth()->user()->current_church_id];
            }
        }

        $churchIds = static::sanitizeChurchIds($churchIds);

        $user->churches()->sync($churchIds);

        $currentChurchId = isset($normalized['current_church_id']) ? (int) $normalized['current_church_id'] : null;

        if (!in_array($currentChurchId, $churchIds, true)) {
            $currentChurchId = $churchIds[0] ?? null;
        }

        if ($user->current_church_id !== $currentChurchId) {
            $user->forceFill([
                'current_church_id' => $currentChurchId,
            ])->save();
        }
    }

    public static function selectedRolesNeedChurch(array $roles): bool
    {
        return !empty(array_intersect($roles, User::LOCAL_ROLES));
    }

    public static function sanitizeRoles(array $roles): array
    {
        $allowedRoles = User::assignableRoleNamesFor(auth()->user());

        return array_values(array_intersect($roles, $allowedRoles));
    }

    public static function sanitizeChurchIds(array $churchIds): array
    {
        $allowedChurchIds = static::allowedChurchIdsForCurrentUser();

        return array_values(array_unique(array_filter(
            array_map(fn($id) => (int) $id, $churchIds),
            fn(int $id) => in_array($id, $allowedChurchIds, true)
        )));
    }

    public static function isPastorManager(): bool
    {
        return auth()->user()?->hasRole('pastor') ?? false;
    }

    public static function getPastorBaseRoleOptions(): array
    {
        return collect(User::PASTOR_ASSIGNABLE_LOCAL_ROLES)
            ->mapWithKeys(fn(string $role) => [$role => static::getRoleLabel($role)])
            ->toArray();
    }

    public static function sanitizePastorBaseRole(?string $role): array
    {
        if (!$role) {
            return [];
        }

        return in_array($role, User::PASTOR_ASSIGNABLE_LOCAL_ROLES, true) ? [$role] : [];
    }

    public static function getAdditionalPermissionOptionsForBaseRole(?string $baseRole): array
    {
        $permissions = static::LOCAL_ROLE_ADDITIONAL_PERMISSIONS[$baseRole] ?? [];

        return collect($permissions)
            ->mapWithKeys(fn(string $permission) => [$permission => static::getAdditionalPermissionLabel($permission)])
            ->toArray();
    }

    public static function sanitizeAdditionalPermissions(?string $baseRole, array $permissions): array
    {
        if (!static::isPastorManager()) {
            return [];
        }

        $allowed = static::LOCAL_ROLE_ADDITIONAL_PERMISSIONS[$baseRole] ?? [];

        return array_values(array_unique(array_filter(
            $permissions,
            fn(string $permission) => in_array($permission, $allowed, true)
        )));
    }

    public static function getDirectAdditionalPermissionsForRecord(User $user): array
    {
        return $user->permissions
            ->pluck('name')
            ->filter(fn(string $permission) => array_key_exists($permission, static::LOCAL_ADDITIONAL_PERMISSION_LABELS))
            ->values()
            ->all();
    }

    public static function getAdditionalPermissionLabel(string $permission): string
    {
        return static::LOCAL_ADDITIONAL_PERMISSION_LABELS[$permission] ?? $permission;
    }

    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'super-admin' => 'Super admin',
            'presidente' => 'Presidente',
            'vicepresidente' => 'Vicepresidente',
            'presbitero' => 'Presbitero',
            'tesorero-global' => 'Tesorero global',
            'pastor' => 'Pastor',
            'contador-local' => 'Contador local',
            'encargado-reuniones' => 'Encargado de reuniones',
            'encargado-seguimiento' => 'Encargado de seguimiento',
            'secretario-registro' => 'Secretario de registro',
            'discipulador' => 'Discipulador',
            default => $role,
        };
    }
}
