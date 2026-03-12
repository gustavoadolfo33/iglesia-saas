<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->foreignId('member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();
            $table->foreignId('assigned_leader_id')
                ->nullable()
                ->constrained('leaders')
                ->nullOnDelete();
            $table->foreignId('status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('city', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('origin_type', 50)->nullable();
            $table->text('source_notes')->nullable();
            $table->date('visit_date')->nullable();
            $table->date('conversion_date')->nullable();
            $table->date('baptism_date')->nullable();
            $table->boolean('is_new_believer')->default(false);
            $table->boolean('needs_pastoral_care')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status_id']);
            $table->index(['church_id', 'assigned_leader_id']);
            $table->index(['church_id', 'origin_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
