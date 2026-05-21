<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'is_military_member') && ! Schema::hasColumn('customers', 'is_student')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('is_military_member')->default(false);
                $table->boolean('is_student')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'is_military_member') && Schema::hasColumn('customers', 'is_student')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('is_military_member');
                $table->dropColumn('is_student');
            });
        }
    }
};
