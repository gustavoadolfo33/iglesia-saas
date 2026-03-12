<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('role', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['church_id', 'user_id']);
            $table->index(['church_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaders');
    }
};
