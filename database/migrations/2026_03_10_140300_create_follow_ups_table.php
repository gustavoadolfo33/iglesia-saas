<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
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
            $table->string('type', 50)->default('call');
            $table->string('status', 30)->default('pending');
            $table->string('priority', 20)->default('medium');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('summary', 180);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'due_at']);
            $table->index(['church_id', 'leader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
