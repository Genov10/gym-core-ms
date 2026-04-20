<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'price',
    'created_at',
    'is_active',
    'is_periodical',
    'day_amount',
    'visit_amount',
])]
class GymService extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'price' => 'float',
            'is_active' => 'boolean',
            'is_periodical' => 'boolean',
            'day_amount' => 'integer',
            'visit_amount' => 'integer',
        ];
    }

    public function customerGymServices(): HasMany
    {
        return $this->hasMany(CustomerGymService::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customers_gym_services')
            ->withPivot(['id', 'created_at', 'expired_at', 'is_active']);
    }
}

