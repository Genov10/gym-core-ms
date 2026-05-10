<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_reference')->unique();
            $table->foreignId('customer_id')->nullable()->index();
            $table->foreignId('gym_service_id')->nullable()->index();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UAH');
            $table->string('status')->default('created'); // created|approved|declined|refunded|unknown

            $table->json('provider_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};

