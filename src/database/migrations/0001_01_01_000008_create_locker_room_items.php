<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locker_room_items', function (Blueprint $table) {
            $table->id();
            $table->integer('locker_number')->nullable();
            $table->foreignId('locker_room_id')->nullable();
            $table->boolean('is_free')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_room_items');
    }
};

