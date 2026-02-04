<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('districts')->insert([
            'name' => 'Distrito Central',
            'code' => 'CENTRAL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
