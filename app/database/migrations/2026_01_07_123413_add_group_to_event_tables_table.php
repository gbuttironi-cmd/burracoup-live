<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_tables', function (Blueprint $table) {
            $table->string('group_code', 10)->nullable()->after('round_id');
        });

        // Sostituisci unique: da (event_id, round_id, table_no) a (event_id, round_id, group_code, table_no)
        DB::statement('ALTER TABLE event_tables DROP CONSTRAINT IF EXISTS event_tables_event_id_round_id_table_no_unique');
        DB::statement('ALTER TABLE event_tables ADD CONSTRAINT event_tables_event_id_round_id_group_code_table_no_unique UNIQUE (event_id, round_id, group_code, table_no)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE event_tables DROP CONSTRAINT IF EXISTS event_tables_event_id_round_id_group_code_table_no_unique');
        DB::statement('ALTER TABLE event_tables ADD CONSTRAINT event_tables_event_id_round_id_table_no_unique UNIQUE (event_id, round_id, table_no)');

        Schema::table('event_tables', function (Blueprint $table) {
            $table->dropColumn('group_code');
        });
    }
};
