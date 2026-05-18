<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gym_services') && ! Schema::hasColumn('gym_services', 'description')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gym_services') && Schema::hasColumn('gym_services', 'description')) {
            Schema::table('gym_services', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
