<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_visits')) {
            // The column was mistakenly created as timestamp in some environments.
            // We don't rely on doctrine/dbal; do a raw ALTER for Postgres.
            DB::statement('ALTER TABLE customer_visits ADD COLUMN locker_room_id INTEGER DEFAULT 0');
            DB::statement('ALTER TABLE customer_visits ADD COLUMN is_finished INTEGER DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_visits')) {
            DB::statement('ALTER TABLE customer_visits DROP COLUMN locker_room_id');
            DB::statement('ALTER TABLE customer_visits DROP COLUMN is_finished');
        }
    }
};

