<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_visits')) {
            // locker_number was mistakenly created as boolean in some environments.
            DB::statement('ALTER TABLE customer_visits ALTER COLUMN locker_number TYPE integer USING (NULL::integer)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_visits')) {
            DB::statement('ALTER TABLE customer_visits ALTER COLUMN locker_number TYPE boolean USING (NULL::boolean)');
        }
    }
};

