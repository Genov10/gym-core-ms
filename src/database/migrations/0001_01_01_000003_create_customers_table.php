<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sex')->nullable();
            $table->string('telegram_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_num_verified')->nullable();
            $table->string('email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

