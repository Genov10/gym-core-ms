<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
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

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => $value ?? '',
            set: fn (?string $value): string => $value ?? '',
        );
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

