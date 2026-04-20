<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'sex',
    'is_staff',
    'locker_amount',
    'create_time',
    'is_active',
])]
class LockerRoom extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
            'locker_amount' => 'integer',
            'create_time' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LockerRoomItem::class);
    }
}

