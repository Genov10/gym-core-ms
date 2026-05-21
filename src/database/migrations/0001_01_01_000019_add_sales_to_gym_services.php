<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gym_services') && ! Schema::hasColumn('gym_services', 'sales_default')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->integer('sales_default')->default(0);
                $table->integer('sales_military_member')->default(0);
                $table->integer('sales_student')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gym_services') && Schema::hasColumn('gym_services', 'sales_default')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->dropColumn('sales_default');
                $table->dropColumn('sales_military_member');
                $table->dropColumn('sales_student');
            });
        }
    }
};
