<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = PermissionSeeder::permissions();

        $map = [
            'super-admin' => $all,
            'presidente' => $all,
            'vicepresidente' => $all,
            'presbitero' => [
                'administracion.view',
                'iglesias.view',
                'reuniones.view',
                'personas.view',
                'seguimientos.view',
                'discipulado.view',
                'formacion.view',
                'reportes.view',
                'reportes.pastoral.view',
            ],
            'tesorero-global' => [
                'administracion.view',
                'iglesias.view',
                'finanzas.view',
                'finanzas.create',
                'finanzas.manage',
                'reportes.view',
                'reportes.finanzas.view',
            ],
            'pastor' => [
                'administracion.view',
                'administracion.users.manage',
                'iglesias.view',
                'reuniones.view',
                'reuniones.create',
                'reuniones.manage',
                'personas.view',
                'personas.manage',
                'seguimientos.view',
                'seguimientos.manage',
                'discipulado.view',
                'discipulado.manage',
                'formacion.view',
                'formacion.courses.manage',
                'formacion.teachers.manage',
                'formacion.enrollments.manage',
                'formacion.progress.manage',
                'reportes.view',
                'reportes.pastoral.view',
            ],
            'contador-local' => [
                'finanzas.view',
                'finanzas.create',
                'finanzas.manage',
                'reportes.view',
                'reportes.finanzas.view',
            ],
            'encargado-reuniones' => [
                'reuniones.view',
                'reuniones.create',
                'reuniones.manage',
            ],
            'encargado-seguimiento' => [
                'personas.view',
                'personas.manage',
                'seguimientos.view',
                'seguimientos.manage',
                'reportes.view',
                'reportes.pastoral.view',
            ],
            'secretario-registro' => [
                'personas.view',
                'personas.manage',
                'reuniones.view',
                'reuniones.create',
            ],
            'discipulador' => [
                'personas.view',
                'discipulado.view',
                'discipulado.manage',
                'formacion.view',
                'formacion.enrollments.manage',
                'formacion.progress.manage',
            ],
            'coordinador-formacion' => [
                'personas.view',
                'discipulado.view',
                'formacion.view',
                'formacion.courses.manage',
                'formacion.teachers.manage',
                'formacion.enrollments.manage',
                'formacion.progress.manage',
            ],
            'docente-formacion' => [
                'personas.view',
                'formacion.view',
                'formacion.enrollments.manage',
                'formacion.progress.manage',
            ],
        ];

        foreach ($map as $roleName => $permissions) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

            if (!$role) {
                continue;
            }

            $role->syncPermissions($permissions);
        }
    }
}
