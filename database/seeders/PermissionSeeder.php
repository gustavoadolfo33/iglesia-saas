<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public static function permissions(): array
    {
        return [
            'administracion.view',
            'administracion.users.manage',
            'iglesias.view',
            'iglesias.create',
            'iglesias.manage',
            'finanzas.view',
            'finanzas.create',
            'finanzas.manage',
            'reuniones.view',
            'reuniones.create',
            'reuniones.manage',
            'personas.view',
            'personas.manage',
            'seguimientos.view',
            'seguimientos.manage',
            'discipulado.view',
            'discipulado.manage',
            'reportes.view',
            'reportes.finanzas.view',
            'reportes.pastoral.view',
            'ver_dashboard_local',
            'ver_finanzas',
            'registrar_finanzas',
            'ver_reuniones',
            'registrar_reuniones',
            'ver_personas',
            'registrar_personas',
            'ver_seguimientos',
            'registrar_seguimientos',
            'ver_discipulado',
            'registrar_discipulado',
            'exportar_reportes',
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (static::permissions() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
