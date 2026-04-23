<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'gym_service_id',
    'created_at',
    'expired_at',
    'is_active',
    'finished_visits_amount',
])]
class CustomerGymService extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'gym_service_id' => 'integer',
            'created_at' => 'datetime',
            'expired_at' => 'datetime',
            'is_active' => 'boolean',
            'finished_visits_amount' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function gymService(): BelongsTo
    {
        return $this->belongsTo(GymService::class);
    }
}

