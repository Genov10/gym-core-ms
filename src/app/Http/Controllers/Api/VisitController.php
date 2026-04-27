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
use Illuminate\Support\Facades\DB;

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

            $notFinishedVisit = CustomerVisit::query()->where('customer_id', $customer->id)->where('is_finished', 0)->first();
            if ($notFinishedVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer already has a not finished visit',
                    'code' => 15,
                ], 400);
            }

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

                if ($lockerRoomId === null || $lockerId === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No free lockers available',
                        'code' => 14,
                    ], 409);
                }

                LockerRoomItem::query()
                    ->where('locker_room_id', $lockerRoomId)
                    ->where('locker_number', $lockerId)
                    ->update([
                        'is_free' => 0,
                    ]);

                CustomerGymService::query()
                    ->where('id', $customerGymService->id)
                    ->update([
                        'finished_visits_amount' => $customerGymService->finished_visits_amount + 1,
                    ]);

                $visit = CustomerVisit::query()->create([
                    'customer_id' => $customer->id,
                    'gym_service_id' => $gymService->id,
                    'start' => Carbon::now(),
                    'locker_number' => $lockerId,
                    'locker_room_id' => $lockerRoomId,
                    'is_finished' => 0,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Visit started successfully',
                    'code' => 0,
                    'data' => [
                        'visit' => base64_encode(json_encode($visit->toArray())),
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

    public function finishVisit(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
        ]);

        try {
            $customer = Customer::query()
                ->where('telegram_id', (int) $data['telegram_id'])
                ->first();

            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                    'code' => 4,
                ], 404);
            }

            $result = DB::transaction(function () use ($customer) {
                $visit = CustomerVisit::query()
                    ->where('customer_id', $customer->id)
                    ->where('is_finished', 0)
                    ->first();

                if (! $visit) {
                    return [
                        'status' => 404,
                        'payload' => [
                            'success' => false,
                            'message' => 'Visit not found',
                            'code' => 16,
                        ],
                    ];
                }

                $visit->finish = Carbon::now();
                $visit->is_finished = 1;
                $visit->save();

                if (! empty($visit->locker_room_id) && ! empty($visit->locker_number)) {
                    LockerRoomItem::query()
                        ->where('locker_room_id', (int) $visit->locker_room_id)
                        ->where('locker_number', (int) $visit->locker_number)
                        ->update([
                            'is_free' => 1,
                        ]);
                }

                return [
                    'status' => 200,
                    'payload' => [
                        'success' => true,
                        'message' => 'Visit finished successfully',
                        'code' => 0,
                    ],
                ];
            });

            return response()->json($result['payload'], $result['status']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Finish visit failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

