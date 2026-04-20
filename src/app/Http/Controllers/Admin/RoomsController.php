<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LockerRoom;
use App\Models\LockerRoomItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoomsController extends Controller
{
    public function index()
    {
        $rooms = LockerRoom::query()
            ->orderByDesc('id')
            ->get();

        return view('admin.rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'in:male,female'],
            'is_staff' => ['nullable', 'boolean'],
            'locker_amount' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($data): void {
            $room = LockerRoom::query()->create([
                'name' => $data['name'],
                'sex' => $data['sex'],
                'is_staff' => (bool) ($data['is_staff'] ?? false),
                'locker_amount' => $data['locker_amount'],
                'create_time' => Carbon::now(),
            ]);

            for ($i = 1; $i <= $data['locker_amount']; $i++) {
                LockerRoomItem::query()->create([
                    'locker_number' => $i,
                    'locker_room_id' => $room->id,
                    'is_free' => true,
                ]);
            }
        });

        return redirect('/admin/rooms')->with('status', 'Комната добавлена.');
    }

    public function destroy(LockerRoom $room)
    {
        DB::transaction(function () use ($room): void {
            LockerRoomItem::query()
                ->where('locker_room_id', $room->id)
                ->delete();

            $room->delete();
        });

        return redirect('/admin/rooms')->with('status', 'Комната удалена.');
    }
}

