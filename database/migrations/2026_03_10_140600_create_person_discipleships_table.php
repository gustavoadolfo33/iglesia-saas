<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_discipleships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->foreignId('person_id')
                ->constrained('persons')
                ->cascadeOnDelete();
            $table->foreignId('leader_id')
                ->nullable()
                ->constrained('leaders')
                ->nullOnDelete();
            $table->foreignId('discipleship_track_id')
                ->constrained('discipleship_tracks')
                ->cascadeOnDelete();
            $table->string('stage', 120)->nullable();
            $table->string('status', 30)->default('active');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'leader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_discipleships');
    }
};
