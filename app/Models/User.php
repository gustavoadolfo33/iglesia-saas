<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public const GLOBAL_ROLES = [
        'super-admin',
        'presidente',
        'vicepresidente',
        'presbitero',
        'tesorero-global',
    ];

    public const LOCAL_ROLES = [
        'pastor',
        'contador-local',
        'encargado-reuniones',
        'encargado-seguimiento',
        'secretario-registro',
        'discipulador',
        'coordinador-formacion',
        'docente-formacion',
    ];

    public const STUDENT_ROLES = [
        'alumno-formacion',
    ];

    public const PASTOR_ASSIGNABLE_LOCAL_ROLES = [
        'contador-local',
        'encargado-reuniones',
        'encargado-seguimiento',
        'secretario-registro',
        'discipulador',
        'coordinador-formacion',
        'docente-formacion',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_church_id',
        'person_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(static::panelRoleNames());
    }

    public function churches()
    {
        return $this->belongsToMany(\App\Models\Church::class)
            ->withPivot(['position', 'is_primary'])
            ->withTimestamps();
    }

    public function currentChurch()
    {
        return $this->belongsTo(\App\Models\Church::class, 'current_church_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function leaderProfiles()
    {
        return $this->hasMany(Leader::class);
    }

    public function createdFollowUps()
    {
        return $this->hasMany(FollowUp::class, 'created_by');
    }

    public function createdFormationTracks()
    {
        return $this->hasMany(FormationTrack::class, 'created_by');
    }

    public function approvedFormationTracks()
    {
        return $this->hasMany(FormationTrack::class, 'approved_by');
    }

    public function createdPersonFormations()
    {
        return $this->hasMany(PersonFormation::class, 'created_by');
    }

    public function reviewedFormationProgress()
    {
        return $this->hasMany(PersonFormationProgress::class, 'reviewed_by');
    }

    public function isGlobalUser(): bool
    {
        return $this->hasAnyRole(array_values(array_diff(static::GLOBAL_ROLES, ['super-admin'])));
    }

    public function isTenantUser(): bool
    {
        return $this->hasAnyRole(static::LOCAL_ROLES);
    }

    public function isFormationStudent(): bool
    {
        return $this->hasRole('alumno-formacion');
    }

    public static function systemRoleNames(): array
    {
        return array_values(array_unique([
            ...static::GLOBAL_ROLES,
            ...static::LOCAL_ROLES,
            ...static::STUDENT_ROLES,
        ]));
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente', 'pastor']);
    }

    public function canCreateChurches(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente']);
    }

    public function canViewChurchManagement(): bool
    {
        return $this->hasRole('super-admin')
            || $this->hasAnyRole(['presidente', 'vicepresidente', 'presbitero', 'tesorero-global']);
    }

    public function canManageChurchManagement(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente']);
    }

    public function canViewFinanceModule(): bool
    {
        return $this->hasAnyPermission([
            'finanzas.view',
            'finanzas.create',
            'finanzas.manage',
            'ver_finanzas',
            'registrar_finanzas',
        ]);
    }

    public function canManageFinanceModule(): bool
    {
        return $this->hasAnyPermission([
            'finanzas.create',
            'finanzas.manage',
            'registrar_finanzas',
        ]);
    }

    public function canViewMeetingsModule(): bool
    {
        return $this->hasAnyPermission([
            'reuniones.view',
            'reuniones.create',
            'reuniones.manage',
            'ver_reuniones',
            'registrar_reuniones',
        ]);
    }

    public function canManageMeetingsModule(): bool
    {
        return $this->hasAnyPermission([
            'reuniones.create',
            'reuniones.manage',
            'registrar_reuniones',
        ]);
    }

    public function canManageMeetingCatalogs(): bool
    {
        return $this->hasAnyPermission(['reuniones.manage'])
            || $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente', 'pastor', 'encargado-reuniones']);
    }

    public function canViewPeopleModule(): bool
    {
        return $this->hasAnyPermission([
            'personas.view',
            'personas.manage',
            'ver_personas',
            'registrar_personas',
        ]);
    }

    public function canManagePeopleModule(): bool
    {
        return $this->hasAnyPermission([
            'personas.manage',
            'registrar_personas',
        ]);
    }

    public function canViewFollowUpsModule(): bool
    {
        return $this->hasAnyPermission([
            'seguimientos.view',
            'seguimientos.manage',
            'ver_seguimientos',
            'registrar_seguimientos',
        ]);
    }

    public function canManageFollowUpsModule(): bool
    {
        return $this->hasAnyPermission([
            'seguimientos.manage',
            'registrar_seguimientos',
        ]);
    }

    public function canViewDiscipleshipModule(): bool
    {
        return $this->hasAnyPermission([
            'discipulado.view',
            'discipulado.manage',
            'ver_discipulado',
            'registrar_discipulado',
        ]);
    }

    public function canManageDiscipleshipModule(): bool
    {
        return $this->hasAnyPermission([
            'discipulado.manage',
            'registrar_discipulado',
        ]);
    }

    public function canViewFormationModule(): bool
    {
        return $this->hasAnyPermission([
            'formacion.view',
            'formacion.courses.manage',
            'formacion.teachers.manage',
            'formacion.enrollments.manage',
            'formacion.progress.manage',
            'discipulado.view',
            'discipulado.manage',
            'ver_discipulado',
            'registrar_discipulado',
        ]) || $this->hasAnyRole(['coordinador-formacion', 'docente-formacion', 'discipulador']);
    }

    public function canManageFormationCourses(): bool
    {
        return $this->hasAnyPermission([
            'formacion.courses.manage',
        ]) || $this->hasAnyRole(['coordinador-formacion']);
    }

    public function canManageFormationTeachers(): bool
    {
        return $this->hasAnyPermission([
            'formacion.teachers.manage',
        ]) || $this->hasAnyRole(['coordinador-formacion']);
    }

    public function canViewFormationEnrollments(): bool
    {
        return $this->canViewFormationModule();
    }

    public function canManageFormationEnrollments(): bool
    {
        return $this->hasAnyPermission([
            'formacion.enrollments.manage',
            'formacion.progress.manage',
        ]) || $this->hasAnyRole(['coordinador-formacion', 'docente-formacion', 'discipulador']);
    }

    public function canManageFormationProgress(): bool
    {
        return $this->hasAnyPermission([
            'formacion.progress.manage',
        ]) || $this->hasAnyRole(['coordinador-formacion', 'docente-formacion', 'discipulador']);
    }

    public function canViewPastoralSettings(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente', 'pastor']);
    }

    public function canManagePastoralSettings(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'vicepresidente', 'pastor']);
    }

    public function canExportReports(): bool
    {
        return $this->hasAnyPermission([
            'reportes.view',
            'reportes.finanzas.view',
            'reportes.pastoral.view',
            'exportar_reportes',
        ]);
    }

    public function canViewMeetingsWidgets(): bool
    {
        return $this->isTenantUser() && $this->canViewMeetingsModule();
    }

    public function canViewPastoralWidgets(): bool
    {
        return $this->isTenantUser() && ($this->canViewPeopleModule() || $this->canViewFollowUpsModule());
    }

    public function canViewExecutiveGlobalDashboard(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente']);
    }

    public function canViewFinancialGlobalDashboard(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'tesorero-global']);
    }

    public function canViewPastoralGlobalDashboard(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presidente', 'presbitero']);
    }

    public function canViewPastoralOperationalGlobalDashboard(): bool
    {
        return $this->hasAnyRole(['super-admin', 'presbitero']);
    }

    public function canViewFinancialOperationalGlobalDashboard(): bool
    {
        return $this->hasAnyRole(['super-admin', 'tesorero-global']);
    }

    public function hasGlobalRoleAssigned(): bool
    {
        return $this->roles->pluck('name')->intersect(static::GLOBAL_ROLES)->isNotEmpty();
    }

    public function hasLocalRoleAssigned(): bool
    {
        return $this->roles->pluck('name')->intersect(static::LOCAL_ROLES)->isNotEmpty();
    }

    public function accessScope(): ?string
    {
        if ($this->hasGlobalRoleAssigned()) {
            return 'global';
        }

        if ($this->hasLocalRoleAssigned()) {
            return 'local';
        }

        return null;
    }

    public function accessScopeLabel(): string
    {
        return match ($this->accessScope()) {
            'global' => 'Global',
            'local' => 'Local',
            default => 'Sin definir',
        };
    }

    public function canViewPresidentDashboard(): bool
    {
        return $this->canViewExecutiveGlobalDashboard();
    }

    public function canViewTreasurerGlobalDashboard(): bool
    {
        return $this->canViewFinancialGlobalDashboard();
    }

    public function canViewPresbyterDashboard(): bool
    {
        return $this->canViewPastoralGlobalDashboard();
    }

    public static function panelRoleNames(): array
    {
        return array_values(array_unique([
            ...static::GLOBAL_ROLES,
            ...static::LOCAL_ROLES,
        ]));
    }

    public static function assignableRoleNamesFor(?self $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->hasRole('super-admin')) {
            return static::panelRoleNames();
        }

        if ($user->hasAnyRole(['presidente', 'vicepresidente'])) {
            return array_values(array_diff(static::panelRoleNames(), ['super-admin']));
        }

        if ($user->hasRole('pastor')) {
            return static::PASTOR_ASSIGNABLE_LOCAL_ROLES;
        }

        return [];
    }
}
