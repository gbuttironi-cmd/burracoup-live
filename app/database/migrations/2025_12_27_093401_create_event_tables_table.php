<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('round_id')->constrained('event_rounds')->cascadeOnDelete();
            $table->foreignId('import_id')->nullable()->constrained('event_imports')->nullOnDelete();

            $table->unsignedInteger('table_no');

            // MVP: salviamo testo già pronto da gestionale
            $table->string('fixed_pair_name')->nullable();  // Danese: coppia fissa
            $table->string('mobile_pair_name')->nullable(); // Danese: coppia mobile
            $table->string('pair_a_name')->nullable();      // alternativa (no Danese)
            $table->string('pair_b_name')->nullable();      // alternativa (no Danese)

            $table->jsonb('extra')->nullable(); // qualsiasi colonna extra del gestionale

            $table->timestamps();

            $table->unique(['event_id', 'round_id', 'table_no']);
            $table->index(['event_id', 'round_id']);
            $table->index(['event_id', 'table_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tables');
    }
};
