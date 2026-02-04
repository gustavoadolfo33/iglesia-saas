<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meeting_types', function (Blueprint $table) {
            $table->id();

            // Multi-tenant: cada iglesia puede tener sus propios tipos
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();

            $table->string('name', 120);
            $table->string('slug', 150);
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Un tipo no se repite dentro de la misma iglesia
            $table->unique(['church_id', 'slug']);
            $table->index('church_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_types');
    }
};