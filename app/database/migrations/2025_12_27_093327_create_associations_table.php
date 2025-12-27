<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('associations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index(['region', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('associations');
    }
};
