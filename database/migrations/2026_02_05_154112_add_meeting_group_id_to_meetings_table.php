<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('meeting_group_id')
                ->nullable()
                ->after('meeting_type_id')
                ->constrained('meeting_groups')
                ->nullOnDelete();

            $table->index('meeting_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['meeting_group_id']);
            $table->dropIndex(['meeting_group_id']);
            $table->dropColumn('meeting_group_id');
        });
    }
};