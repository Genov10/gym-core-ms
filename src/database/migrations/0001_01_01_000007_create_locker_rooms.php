<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locker_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sex')->nullable();
            $table->boolean('is_staff')->nullable();
            $table->integer('locker_amount')->nullable();
            $table->timestamp('create_time')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_rooms');
    }
};

