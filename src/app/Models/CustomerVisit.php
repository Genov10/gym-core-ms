<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'gym_service_id',
    'start',
    'finish',
    'locker_number',
])]
class CustomerVisit extends Model
{
    protected $table = 'customers_visits';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'gym_service_id' => 'integer',
            'start' => 'datetime',
            'finish' => 'datetime',
            'locker_number' => 'boolean',
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

