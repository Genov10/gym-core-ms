<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gym_services')) {
            return;
        }

        Schema::table('gym_services', function (Blueprint $table) {
            if (! Schema::hasColumn('gym_services', 'can_be_extended')) {
                $table->integer('can_be_extended')->default(0);
            }
            if (! Schema::hasColumn('gym_services', 'sale_for_next')) {
                $table->integer('sale_for_next')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('gym_services')) {
            return;
        }

        Schema::table('gym_services', function (Blueprint $table) {
            if (Schema::hasColumn('gym_services', 'can_be_extended')) {
                $table->dropColumn('can_be_extended');
            }
            if (Schema::hasColumn('gym_services', 'sale_for_next')) {
                $table->dropColumn('sale_for_next');
            }
        });
    }
};
