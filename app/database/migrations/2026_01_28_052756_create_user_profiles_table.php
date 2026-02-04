<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->primary();

            // Informazioni di base (usate anche per eventi)
            $table->string('public_name')->nullable();
            $table->string('type')->default('associazione'); // associazione|persona
            $table->string('city')->nullable();

            $table->string('contact_email')->nullable();

            $table->string('timezone')->default('Europe/Rome');
            $table->unsignedSmallInteger('default_refresh_seconds')->default(120);

            $table->jsonb('branding_json')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};