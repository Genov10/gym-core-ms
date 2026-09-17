<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gym_services') && ! Schema::hasColumn('gym_services', 'freeze_day_amount')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->integer('freeze_day_amount')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gym_services') && Schema::hasColumn('gym_services', 'freeze_day_amount')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->dropColumn('freeze_day_amount');
            });
        }
    }
};
