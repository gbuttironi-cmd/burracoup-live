<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('association_id')->constrained('associations')->cascadeOnDelete();

            $table->string('title');
            $table->string('type')->default('circolo'); // circolo|regionale|nazionale
            $table->string('region')->nullable();
            $table->string('city')->nullable();

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->jsonb('info_pre_evento')->nullable(); // convenzioni, contatti, note, ecc.
            $table->string('status')->default('draft');   // draft|published|archived

            $table->timestamps();

            $table->index(['start_at']);
            $table->index(['association_id']);
            $table->index(['region']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
