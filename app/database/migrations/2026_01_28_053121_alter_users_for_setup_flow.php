<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // se non esistono già, aggiungiamo i campi necessari al flusso
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('invited');
            }

            if (!Schema::hasColumn('users', 'profile_completed')) {
                $table->boolean('profile_completed')->default(false);
            }

            if (!Schema::hasColumn('users', 'setup_token_hash')) {
                $table->string('setup_token_hash')->nullable()->index();
            }

            if (!Schema::hasColumn('users', 'setup_expires_at')) {
                $table->timestampTz('setup_expires_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'owner_key_hash')) {
                $table->string('owner_key_hash')->nullable()->unique();
            }

            if (!Schema::hasColumn('users', 'display_name')) {
                $table->string('display_name')->nullable();
            }

            if (!Schema::hasColumn('users', 'admin_note')) {
                $table->text('admin_note')->nullable();
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // rollback “soft”: togliamo solo i campi che abbiamo aggiunto
            $cols = [
                'status',
                'profile_completed',
                'setup_token_hash',
                'setup_expires_at',
                'owner_key_hash',
                'display_name',
                'admin_note',
                'is_active',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};