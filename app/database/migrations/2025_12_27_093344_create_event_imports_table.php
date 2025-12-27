<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            $table->string('source_type');  // csv|xlsx|paste
            $table->string('source_name')->nullable(); // nome file o descrizione
            $table->string('import_kind')->default('all'); // tables|standings|all
            $table->string('imported_by')->nullable(); // per ora string, poi user_id

            $table->jsonb('raw_payload')->nullable(); // conserva l’input “com’è arrivato”
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['event_id', 'created_at']);
            $table->index(['import_kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_imports');
    }
};
