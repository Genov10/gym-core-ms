<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\CustomerVisit;
use App\Models\GymService;
use App\Models\LockerRoom;
use App\Models\LockerRoomItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitController extends Controller
{
    public function startVisit(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => ['nullable', 'integer'],
            'service_id' => ['required', 'integer'],
        ]);

        try {
            $customer = Customer::query()
                ->where('telegram_id', (int) $data['telegram_id'])
                ->first();

            $customerGymService = CustomerGymService::query()
                ->where('customer_id', (int) $customer->id)
                ->where('gym_service_id', (int) $data['service_id'])
                ->where('is_active', 1)
                ->first();


            $gymService = GymService::query()
                ->where('id', (int) $data['service_id'])
                ->first();

            $is_periodical = $gymService->is_periodical;
            $can_pass = false;
            if ($is_periodical) {
                $can_pass = true;
            } else {
                $numberOfVisitsRemaining = $gymService->visit_amount - $customerGymService->finished_visits_amount;
                if ($numberOfVisitsRemaining >= 0) {
                    $can_pass = true;
                }
                if ($numberOfVisitsRemaining <= 0) {
                    $expired_at = Carbon::now();
                    CustomerGymService::query()
                        ->where('id', $customerGymService->id)
                        ->update([
                            'is_active' => 0,
                            'expired_at' => $expired_at,
                        ]);
                }

            }

            if ($can_pass) {
                $lockerRoom = LockerRoom::query()
                    ->where('sex', $customer->sex)
                    ->where('is_staff', 0)
                    ->where('is_active', 1)
                    ->get();

                $lockerRoomId = null;
                $lockerId = null;
                foreach ($lockerRoom as $room) {
                    $lockerRoomItem = LockerRoomItem::query()
                        ->where('locker_room_id', $room->id)
                        ->where('is_free', 1)
                        ->first();

                    if ($lockerRoomItem) {
                        $lockerId = $lockerRoomItem->locker_number;
                        $lockerRoomId = $room->id;
                        break;
                    }
                }

                LockerRoomItem::query()
                    ->where('locker_room_id', $lockerRoomId)
                    ->where('locker_number', $lockerId)
                    ->update([
                        'is_free' => 0,
                    ]);

                $visit = CustomerVisit::query()->create([
                    'customer_id' => $customer->id,
                    'gym_service_id' => $gymService->id,
                    'start' => Carbon::now(),
                    'locker_number' => $lockerId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Visit started successfully',
                    'code' => 0,
                    'data' => [
                        'visit' =>  base64_encode($visit->id),
                    ],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit not allowed',
                    'code' => 4,
                ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order create failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

