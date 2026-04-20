<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'locker_number',
    'locker_room_id',
    'is_free',
])]
class LockerRoomItem extends Model
{
    protected $table = 'locker_room_items';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'locker_room_id' => 'integer',
            'locker_number' => 'datetime',
            'is_free' => 'boolean',
        ];
    }

    public function lockerRoom(): BelongsTo
    {
        return $this->belongsTo(LockerRoom::class);
    }
}

