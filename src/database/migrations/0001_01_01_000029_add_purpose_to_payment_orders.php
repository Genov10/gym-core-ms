<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_orders')) {
            return;
        }

        if (! Schema::hasColumn('payment_orders', 'purpose')) {
            Schema::table('payment_orders', function (Blueprint $table) {
                $table->string('purpose', 32)->default('purchase')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_orders')) {
            return;
        }

        if (Schema::hasColumn('payment_orders', 'purpose')) {
            Schema::table('payment_orders', function (Blueprint $table) {
                $table->dropColumn('purpose');
            });
        }
    }
};
