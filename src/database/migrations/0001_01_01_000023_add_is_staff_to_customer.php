<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'is_staff')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('is_staff')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'is_staff')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('is_staff');
            });
        }
    }
};
