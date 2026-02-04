<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChurchUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $userId = 1; // el primer usuario que creaste en /register
        $churchId = DB::table('churches')->where('slug', 'iglesia-central-la-paz')->value('id');

        DB::table('church_user')->insert([
            'church_id' => $churchId,
            'user_id' => $userId,
            'position' => 'Pastor',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
