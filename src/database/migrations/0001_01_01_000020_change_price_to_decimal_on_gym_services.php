<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gym_services') || ! Schema::hasColumn('gym_services', 'price')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gym_services ALTER COLUMN price TYPE NUMERIC(10, 2) USING ROUND(price::numeric, 2)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('gym_services') || ! Schema::hasColumn('gym_services', 'price')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gym_services ALTER COLUMN price TYPE DOUBLE PRECISION USING price::double precision');
        }
    }
};
