<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $churchId = DB::table('churches')
            ->where('slug', 'iglesia-central-la-paz')
            ->value('id');

        $user = User::firstOrCreate(
            ['email' => 'admin@iglesia.test'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password'),
            ]
        );

        Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        if ($churchId) {
            $user->current_church_id = $churchId;
            $user->save();
        }

        $user->syncRoles(['super-admin']);
    }
}