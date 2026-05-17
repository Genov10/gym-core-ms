<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            // The column was mistakenly created as timestamp in some environments.
            // We don't rely on doctrine/dbal; do a raw ALTER for Postgres.
            DB::statement('ALTER TABLE customers ADD COLUMN username VARCHAR(255) DEFAULT null');
            DB::statement('ALTER TABLE customers ADD COLUMN lastname VARCHAR(255) DEFAULT null');
            DB::statement('UPDATE customers SET username = name');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            DB::statement('ALTER TABLE customers DROP COLUMN username');
            DB::statement('ALTER TABLE customers DROP COLUMN lastname');
        }
    }
};

