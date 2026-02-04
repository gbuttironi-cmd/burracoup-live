<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();

            // Dati base (informazioni di base)
            $table->string('name');
            $table->string('type')->default('associazione'); // associazione | persona
            $table->string('slug')->nullable()->index();

            $table->string('contact_email')->nullable()->index();

            // Configurazione di default
            $table->string('timezone')->default('Europe/Rome');
            $table->unsignedSmallInteger('default_refresh_seconds')->default(120);

            // Branding (logo, colori, footer, ecc.)
            $table->jsonb('branding_json')->nullable();

            // Sicurezza: token salvato SOLO hashato
            $table->string('owner_key_hash')->unique();

            // Stato
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};