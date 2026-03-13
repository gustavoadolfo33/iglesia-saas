<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropIndex(['church_id', 'household_id']);
            $table->dropColumn('household_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('household_id')
                ->nullable()
                ->after('church_id')
                ->constrained('households')
                ->nullOnDelete();

            $table->index(['church_id', 'household_id']);
        });
    }
};
