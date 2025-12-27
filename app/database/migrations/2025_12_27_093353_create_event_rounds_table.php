<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            $table->unsignedInteger('round_no'); // 1..N
            $table->string('name')->nullable();  // es. "Turno 1"
            $table->string('status')->default('draft'); // draft|published

            $table->timestamps();

            $table->unique(['event_id', 'round_no']);
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rounds');
    }
};
