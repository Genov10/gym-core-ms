<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gym_services') || ! Schema::hasColumn('gym_services', 'can_be_extended')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended DROP DEFAULT');
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended TYPE integer USING (can_be_extended::integer)');
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended SET DEFAULT 0');
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended SET NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('gym_services') || ! Schema::hasColumn('gym_services', 'can_be_extended')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended DROP DEFAULT');
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended TYPE boolean USING (can_be_extended > 0)');
            DB::statement('ALTER TABLE gym_services ALTER COLUMN can_be_extended SET DEFAULT false');
        }
    }
};
