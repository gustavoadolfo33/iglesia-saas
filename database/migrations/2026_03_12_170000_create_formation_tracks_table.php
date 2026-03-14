<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formation_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->nullable()->constrained('churches')->nullOnDelete();
            $table->string('scope_type', 20);
            $table->string('scope_label', 150)->nullable();
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('category', 30);
            $table->string('level', 20);
            $table->boolean('affects_pastoral_flow')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('church_id');
            $table->index('scope_type');
            $table->index('category');
            $table->index('level');
            $table->index('is_active');
            $table->unique(
                ['church_id', 'slug'],
                'ft_church_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_tracks');
    }
};
