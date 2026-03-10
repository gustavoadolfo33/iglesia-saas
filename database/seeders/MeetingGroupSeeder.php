<?php

namespace Database\Seeders;

use App\Models\Church;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeetingGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'General',
            'Música',
            'Discipulado',
            'Jóvenes',
            'Mujeres',
            'Niños',
            'Intercesión',
        ];

        $now = now();

        foreach (Church::all() as $church) {
            foreach ($groups as $name) {
                DB::table('meeting_groups')->updateOrInsert(
                    [
                        'church_id' => $church->id,
                        'name' => $name,
                    ],
                    [
                        'active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}