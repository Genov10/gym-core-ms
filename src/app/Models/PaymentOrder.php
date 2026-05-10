<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_reference',
    'customer_id',
    'gym_service_id',
    'amount',
    'currency',
    'status',
    'provider_payload',
])]
class PaymentOrder extends Model
{
    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'gym_service_id' => 'integer',
            'amount' => 'float',
            'provider_payload' => 'array',
        ];
    }
}

