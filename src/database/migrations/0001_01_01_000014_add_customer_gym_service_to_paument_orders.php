<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_orders')) {
            // The column was mistakenly created as timestamp in some environments.
            // We don't rely on doctrine/dbal; do a raw ALTER for Postgres.
            DB::statement('ALTER TABLE payment_orders ADD COLUMN customer_gym_service_id INTEGER DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_orders')) {
            DB::statement('ALTER TABLE payment_orders DROP COLUMN customer_gym_service_id');
        }
    }
};

