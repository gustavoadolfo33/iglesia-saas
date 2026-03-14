<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formation_track_leaders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_track_id')->constrained('formation_tracks')->cascadeOnDelete();
            $table->foreignId('leader_id')->constrained('leaders')->cascadeOnDelete();
            $table->string('role', 20);
            $table->timestamps();

            $table->unique(
                ['formation_track_id', 'leader_id'],
                'ftl_track_leader_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_track_leaders');
    }
};
