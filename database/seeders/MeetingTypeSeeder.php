<?php

namespace Database\Seeders;

use App\Models\Church;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeetingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Dominical',
            'Discipulado',
            'Oración',
            'Jóvenes',
            'Mujeres',
            'Evangelismo',
            'Evento',
        ];

        $now = now();

        foreach (Church::all() as $church) {
            foreach ($types as $name) {
                DB::table('meeting_types')->updateOrInsert(
                    [
                        'church_id' => $church->id,
                        'slug' => Str::slug($name),
                    ],
                    [
                        'name' => $name,
                        'active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}