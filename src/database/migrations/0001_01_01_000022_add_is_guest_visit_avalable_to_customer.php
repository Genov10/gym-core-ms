<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer') && ! Schema::hasColumn('customer', 'is_guest_visit_avalable')) {
            DB::statement('ALTER TABLE customer ADD COLUMN is_guest_visit_avalable BOOLEAN DEFAULT TRUE');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer') && Schema::hasColumn('customer', 'is_guest_visit_avalable')) {
            DB::statement('ALTER TABLE customer DROP COLUMN is_guest_visit_avalable');
        }
    }
};

