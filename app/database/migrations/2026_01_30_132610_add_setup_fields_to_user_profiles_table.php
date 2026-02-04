<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // dati “associazione”
            $table->string('presidente')->nullable();
            $table->string('telefono')->nullable();

            // email proposta (admin), la salviamo come “contatto”
            $table->string('email_contatto')->nullable();

            $table->string('nome_associazione')->nullable();
            $table->string('regione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('citta')->nullable();
            $table->string('sede_indirizzo')->nullable();

            // più opzioni → jsonb
            $table->jsonb('giorni_orari')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'presidente',
                'telefono',
                'email_contatto',
                'nome_associazione',
                'regione',
                'provincia',
                'citta',
                'sede_indirizzo',
                'giorni_orari',
            ]);
        });
    }
};