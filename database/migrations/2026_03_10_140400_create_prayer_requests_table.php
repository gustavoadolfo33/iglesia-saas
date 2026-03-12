<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table) {
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
            $table->string('title', 150);
            $table->text('request');
            $table->string('status', 30)->default('open');
            $table->boolean('is_confidential')->default(false);
            $table->dateTime('requested_at')->nullable();
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_requests');
    }
};
