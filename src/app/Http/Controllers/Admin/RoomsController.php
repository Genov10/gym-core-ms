<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerVisit;
use App\Models\LockerRoom;
use App\Models\LockerRoomItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function show(LockerRoom $room)
    {
        $items = LockerRoomItem::query()
            ->where('locker_room_id', $room->id)
            ->orderBy('locker_number')
            ->get();

        $activeVisits = CustomerVisit::query()
            ->where('locker_room_id', $room->id)
            ->where('is_finished', false)
            ->with('customer')
            ->get()
            ->keyBy('locker_number');

        $lockers = $items->map(function (LockerRoomItem $item) use ($activeVisits) {
            $visit = $activeVisits->get($item->locker_number);

            return [
                'number' => $item->locker_number,
                'is_free' => (bool) $item->is_free,
                'visit' => $visit,
            ];
        });

        return view('admin.rooms.show', [
            'room' => $room,
            'lockers' => $lockers,
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

        return redirect('/admin/rooms')->with('status', 'Раздевалка добавлена.');
    }

    public function update(Request $request, LockerRoom $room)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'in:male,female'],
            'is_staff' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'locker_amount' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($data, $room): void {
            $this->syncLockerItems($room, (int) $data['locker_amount']);

            $room->update([
                'name' => $data['name'],
                'sex' => $data['sex'],
                'is_staff' => (bool) ($data['is_staff'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? false),
                'locker_amount' => (int) $data['locker_amount'],
            ]);
        });

        return redirect('/admin/rooms/'.$room->id)->with('status', 'Раздевалка сохранена.');
    }

    private function syncLockerItems(LockerRoom $room, int $newAmount): void
    {
        $currentMax = (int) LockerRoomItem::query()
            ->where('locker_room_id', $room->id)
            ->max('locker_number');

        if ($newAmount > $currentMax) {
            for ($i = $currentMax + 1; $i <= $newAmount; $i++) {
                LockerRoomItem::query()->create([
                    'locker_number' => $i,
                    'locker_room_id' => $room->id,
                    'is_free' => true,
                ]);
            }

            return;
        }

        if ($newAmount >= $currentMax) {
            return;
        }

        $occupiedToRemove = LockerRoomItem::query()
            ->where('locker_room_id', $room->id)
            ->where('locker_number', '>', $newAmount)
            ->where('is_free', false)
            ->exists();

        if ($occupiedToRemove) {
            throw ValidationException::withMessages([
                'locker_amount' => 'Нельзя уменьшить количество: есть занятые шкафчики сверх нового лимита.',
            ]);
        }

        LockerRoomItem::query()
            ->where('locker_room_id', $room->id)
            ->where('locker_number', '>', $newAmount)
            ->delete();
    }
}
