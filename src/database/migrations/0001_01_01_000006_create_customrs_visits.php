<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('gym_service_id')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('finish')->nullable();
            $table->boolean('locker_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_visits');
    }
};

