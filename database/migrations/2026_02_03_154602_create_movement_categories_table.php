<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movement_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->nullable()
                ->constrained('churches')
                ->nullOnDelete(); // si se borra iglesia, categorías globales quedan

            // income | expense
            $table->string('type', 20);

            $table->string('name', 120);

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Evita duplicados por iglesia + tipo + nombre
            $table->unique(['church_id', 'type', 'name']);
            $table->index(['church_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_categories');
    }
};
