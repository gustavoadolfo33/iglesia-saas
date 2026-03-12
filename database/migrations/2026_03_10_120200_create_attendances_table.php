<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();
            $table->foreignId('meeting_id')
                ->constrained('meetings')
                ->cascadeOnDelete();
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();
            $table->boolean('attended')->default(true);
            $table->timestamp('recorded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['church_id', 'meeting_id', 'member_id']);
            $table->index(['church_id', 'meeting_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
