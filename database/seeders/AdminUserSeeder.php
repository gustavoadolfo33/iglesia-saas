<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
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

        // Setear iglesia activa si existe
        if ($churchId) {
            $user->current_church_id = $churchId;
            $user->save();
        }
        // Rol global total
        $user->assignRole('presidente');
    }
}
