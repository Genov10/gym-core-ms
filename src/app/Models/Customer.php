<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'sex',
    'telegram_id',
    'created_at',
    'phone',
    'is_num_verified',
    'email',
])]
class Customer extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_num_verified' => 'boolean',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(CustomerVisit::class);
    }

    public function customerGymServices(): HasMany
    {
        return $this->hasMany(CustomerGymService::class);
    }

    public function gymServices(): BelongsToMany
    {
        return $this->belongsToMany(GymService::class, 'customers_gym_services')
            ->withPivot(['id', 'created_at', 'expired_at', 'is_active']);
    }
}

