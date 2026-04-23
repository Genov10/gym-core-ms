<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_gym_services')) {
            // The column was mistakenly created as timestamp in some environments.
            // We don't rely on doctrine/dbal; do a raw ALTER for Postgres.
            DB::statement('ALTER TABLE customer_gym_services ADD COLUMN finished_visits_amount INTEGER DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_gym_services')) {
            DB::statement('ALTER TABLE customer_gym_services DROP COLUMN finished_visits_amount');
        }
    }
};

