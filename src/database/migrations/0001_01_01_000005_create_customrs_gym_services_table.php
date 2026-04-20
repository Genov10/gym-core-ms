<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_gym_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('gym_service_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->boolean('is_active')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_gym_services');
    }
};

