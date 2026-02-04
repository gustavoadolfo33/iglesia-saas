<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChurchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $districtId = DB::table('districts')->where('code', 'CENTRAL')->value('id');

        DB::table('churches')->insert([
            [
                'district_id' => $districtId,
                'name' => 'Iglesia Central La Paz',
                'slug' => 'iglesia-central-la-paz',
                'status' => 'active',
                'address' => 'Zona Central',
                'city' => 'La Paz',
                'phone' => '00000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'district_id' => $districtId,
                'name' => 'Iglesia El Alto',
                'slug' => 'iglesia-el-alto',
                'status' => 'active',
                'address' => 'Av. Juan Pablo II',
                'city' => 'El Alto',
                'phone' => '00000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
