<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('category', 50)->default('visitor');
            $table->string('color', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['church_id', 'slug']);
            $table->index(['church_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
