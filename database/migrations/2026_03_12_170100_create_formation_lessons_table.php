<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formation_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_track_id')->constrained('formation_tracks')->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('content_type', 20);
            $table->longText('content_body')->nullable();
            $table->string('content_url')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('formation_track_id');
            $table->index('sort_order');
            $table->index('is_active');
            $table->unique(
                ['formation_track_id', 'slug'],
                'fl_track_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_lessons');
    }
};
