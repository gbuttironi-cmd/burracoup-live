<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // audit minimale
            if (!Schema::hasColumn('users', 'setup_completed_at')) {
                $table->timestampTz('setup_completed_at')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestampTz('last_seen_at')->nullable()->index();
            }

            // soft delete
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletesTz();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'setup_completed_at')) $table->dropColumn('setup_completed_at');
            if (Schema::hasColumn('users', 'last_seen_at')) $table->dropColumn('last_seen_at');
            if (Schema::hasColumn('users', 'deleted_at')) $table->dropSoftDeletesTz();
        });
    }
};