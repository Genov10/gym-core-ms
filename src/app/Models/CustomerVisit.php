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
    'locker_room_id',
    'is_finished',
])]
class CustomerVisit extends Model
{
    protected $table = 'customer_visits';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'gym_service_id' => 'integer',
            'start' => 'datetime',
            'finish' => 'datetime',
            'locker_number' => 'integer',
            'locker_room_id' => 'integer',
            'is_finished' => 'boolean',
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

    public function lockerRoom(): BelongsTo
    {
        return $this->belongsTo(LockerRoom::class);
    }
}

