<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_formation_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_formation_id')->constrained('person_formations')->cascadeOnDelete();
            $table->foreignId('formation_lesson_id')->constrained('formation_lessons')->cascadeOnDelete();
            $table->string('status', 20);
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('person_formation_id');
            $table->index('formation_lesson_id');
            $table->index('status');
            $table->unique(['person_formation_id', 'formation_lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_formation_progress');
    }
};
