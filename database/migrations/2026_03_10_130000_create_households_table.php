<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();

            $table->index('church_id');
            $table->unique(['church_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
