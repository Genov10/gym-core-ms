<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('locker_room_items')) {
            // The column was mistakenly created as timestamp in some environments.
            // We don't rely on doctrine/dbal; do a raw ALTER for Postgres.
            DB::statement('ALTER TABLE locker_room_items ALTER COLUMN locker_number TYPE integer USING (NULL::integer)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('locker_room_items')) {
            DB::statement('ALTER TABLE locker_room_items ALTER COLUMN locker_number TYPE timestamp(0) without time zone USING (NULL::timestamp)');
        }
    }
};

