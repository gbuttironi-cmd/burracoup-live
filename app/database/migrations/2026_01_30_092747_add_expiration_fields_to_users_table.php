<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('inactive_expired_at')->nullable();
            $table->string('deactivated_reason', 32)->nullable()->index(); // expired|inactive|manual|deleted
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'expires_at',
                'expired_at',
                'inactive_expired_at',
                'deactivated_reason',
            ]);
        });
    }
};