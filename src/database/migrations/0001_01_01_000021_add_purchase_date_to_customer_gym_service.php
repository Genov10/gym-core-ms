<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_gym_services') && ! Schema::hasColumn('customer_gym_services', 'purchase_date')) {
            DB::statement('ALTER TABLE customer_gym_services ADD COLUMN purchase_date TIMESTAMP DEFAULT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_gym_services') && Schema::hasColumn('customer_gym_services', 'purchase_date')) {
            DB::statement('ALTER TABLE customer_gym_services DROP COLUMN purchase_date');
        }
    }
};

