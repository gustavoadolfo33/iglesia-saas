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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete();

            // quién registró el movimiento
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('date');

            // income | expense
            $table->string('type', 20);

            // categoría elegida del catálogo (opcional por si a futuro permites texto libre)
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('movement_categories')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);

            // “observación” / detalle
            $table->string('description', 255)->nullable();

            // nro recibo interno / referencia / comprobante (si aplica)
            $table->string('reference', 80)->nullable();

            $table->timestamps();

            $table->index(['church_id', 'date']);
            $table->index(['church_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
