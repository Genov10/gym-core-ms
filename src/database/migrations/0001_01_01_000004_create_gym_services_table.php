<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->float('price')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->boolean('is_active')->nullable();
            $table->boolean('is_periodical')->nullable();
            $table->integer('day_amount')->nullable();
            $table->integer('visit_amount')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_services');
    }
};

