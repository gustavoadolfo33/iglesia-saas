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
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')
                ->nullable()
                ->constrained('districts')
                ->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();

            $table->string('status', 20)->default('active');

            $table->string('address', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('phone', 50)->nullable();

            $table->timestamps();

            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
