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
    'purchase_date',
    'freeze_start',
    'freeze_end',
    'freeze_days_used',
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
            'purchase_date' => 'datetime',
            'is_active' => 'boolean',
            'finished_visits_amount' => 'integer',
            'freeze_start' => 'datetime',
            'freeze_end' => 'datetime',
            'freeze_days_used' => 'integer',
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

