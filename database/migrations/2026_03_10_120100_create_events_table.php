<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('location', 150)->nullable();
            $table->timestamps();

            $table->index('church_id');
            $table->index(['church_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
