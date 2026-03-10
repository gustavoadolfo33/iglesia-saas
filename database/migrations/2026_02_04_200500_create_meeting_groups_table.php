<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meeting_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();

            $table->string('name', 120);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['church_id', 'name']);
            $table->index('church_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_groups');
    }
};