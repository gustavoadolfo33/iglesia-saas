<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();

            $table->foreignId('meeting_type_id')
                ->constrained('meeting_types')
                ->cascadeOnDelete();



            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('topic', 200)->nullable();
            $table->string('bible_reference', 200)->nullable();
            $table->string('leader_name', 120)->nullable(); // responsable
            $table->string('guest', 120)->nullable(); // invitado

            $table->unsignedInteger('attendees_count')->default(0);
            $table->unsignedInteger('visitors_count')->default(0);

            $table->string('status', 20)->default('done'); // planned | done | cancelled
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(['church_id', 'date']);
            $table->index('meeting_type_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};