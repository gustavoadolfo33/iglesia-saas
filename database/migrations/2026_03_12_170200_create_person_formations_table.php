<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->nullable()->constrained('churches')->nullOnDelete();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('formation_track_id')->constrained('formation_tracks')->cascadeOnDelete();
            $table->foreignId('leader_id')->nullable()->constrained('leaders')->nullOnDelete();
            $table->string('status', 20);
            $table->date('started_at');
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('church_id');
            $table->index('person_id');
            $table->index('formation_track_id');
            $table->index('leader_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_formations');
    }
};
