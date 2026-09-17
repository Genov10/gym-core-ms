<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_gym_services')) {
            return;
        }

        Schema::table('customer_gym_services', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_gym_services', 'freeze_start')) {
                $table->timestamp('freeze_start')->nullable();
            }
            if (! Schema::hasColumn('customer_gym_services', 'freeze_end')) {
                $table->timestamp('freeze_end')->nullable();
            }
            if (! Schema::hasColumn('customer_gym_services', 'freeze_days_used')) {
                $table->integer('freeze_days_used')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_gym_services')) {
            return;
        }

        Schema::table('customer_gym_services', function (Blueprint $table) {
            if (Schema::hasColumn('customer_gym_services', 'freeze_start')) {
                $table->dropColumn('freeze_start');
            }
            if (Schema::hasColumn('customer_gym_services', 'freeze_end')) {
                $table->dropColumn('freeze_end');
            }
            if (Schema::hasColumn('customer_gym_services', 'freeze_days_used')) {
                $table->dropColumn('freeze_days_used');
            }
        });
    }
};
