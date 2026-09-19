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
            if (! Schema::hasColumn('customer_gym_services', 'was_extended')) {
                $table->boolean('was_extended')->default(false);
            }
            if (! Schema::hasColumn('customer_gym_services', 'extend_timestamp')) {
                $table->timestamp('extend_timestamp')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_gym_services')) {
            return;
        }

        Schema::table('customer_gym_services', function (Blueprint $table) {
            if (Schema::hasColumn('customer_gym_services', 'was_extended')) {
                $table->dropColumn('was_extended');
            }
            if (Schema::hasColumn('customer_gym_services', 'extend_timestamp')) {
                $table->dropColumn('extend_timestamp');
            }
        });
    }
};
