<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();

            $table->index('church_id');
            $table->index(['church_id', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
