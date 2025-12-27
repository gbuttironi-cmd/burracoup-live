<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            // round_id NULL = classifica finale
            $table->foreignId('round_id')->nullable()->constrained('event_rounds')->nullOnDelete();
            $table->foreignId('import_id')->nullable()->constrained('event_imports')->nullOnDelete();

            $table->unsignedInteger('rank')->nullable();
            $table->string('competitor_name'); // MVP: nome coppia/competitore come testo
            $table->decimal('points', 10, 2)->nullable();

            $table->jsonb('extra')->nullable(); // vps, spareggi, boards, ecc.

            $table->timestamps();

            $table->index(['event_id', 'round_id']);
            $table->index(['event_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_standings');
    }
};
