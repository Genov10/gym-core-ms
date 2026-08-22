<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Providers\CustomerProvider;
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
    use ChecksCustomerBan;

    public function startVisit(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
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

            if ($banResponse = $this->denyBannedCustomer($customer)) {
                return $banResponse;
            }

            $serviceId = (int) $data['service_id'];

            // Staff pass: only is_staff check, no lockers / services / unfinished-visit gates.
            if ($serviceId === -1) {
                if (! $customer->is_staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Staff visit not available',
                        'code' => 4,
                    ], 400);
                }

                $visit = CustomerVisit::query()->create([
                    'customer_id' => (int) $customer->id,
                    'gym_service_id' => -1,
                    'start' => Carbon::now(),
                    'locker_number' => null,
                    'locker_room_id' => null,
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
            }

            $notFinishedVisit = CustomerVisit::query()->where('customer_id', $customer->id)->where('is_finished', 0)->first();
            if ($notFinishedVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer already has a not finished visit',
                    'code' => 15,
                ], 400);
            }

            if ($serviceId === 0) {
                if (CustomerProvider::isGuestVisitAvailable((int) $customer->id) !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guest visit not available',
                        'code' => 4,
                    ], 400);
                }

                $result = $this->startSpecialVisit($customer, gymServiceId: 0);

                return response()->json($result['payload'], $result['status']);
            }

            $gymService = GymService::query()
                ->where('id', $serviceId)
                ->first();

            if (! $gymService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gym service not found',
                    'code' => 4,
                ], 404);
            }

            $result = DB::transaction(function () use ($customer, $gymService) {
                $customerGymService = CustomerGymService::query()
                    ->where('customer_id', (int) $customer->id)
                    ->where('gym_service_id', (int) $gymService->id)
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (! $customerGymService) {
                    return [
                        'status' => 404,
                        'payload' => [
                            'success' => false,
                            'message' => 'Active customer service not found',
                            'code' => 4,
                        ],
                    ];
                }

                $isPeriodical = (bool) $gymService->is_periodical;

                if (! $customerGymService->created_at) {
                    $customerGymService->created_at = Carbon::now();

                    if ($isPeriodical) {
                        $customerGymService->expired_at = Carbon::now()->addDays((int) $gymService->day_amount);
                    }
                    $customerGymService->save();
                }

                if ($isPeriodical && $customerGymService->expired_at && $customerGymService->expired_at->lt(Carbon::now())) {
                    $customerGymService->is_active = false;
                    $customerGymService->save();

                    return [
                        'status' => 400,
                        'payload' => [
                            'success' => false,
                            'message' => 'Subscription expired',
                            'code' => 4,
                        ],
                    ];
                }

                if (! $isPeriodical) {
                    $visitAmount = (int) $gymService->visit_amount;
                    $finished = (int) $customerGymService->finished_visits_amount;
                    $remaining = $visitAmount - $finished;

                    if ($remaining <= 0) {
                        // No visits left, deactivate and block.
                        $customerGymService->is_active = 0;
                        $customerGymService->expired_at = Carbon::now();
                        $customerGymService->save();

                        return [
                            'status' => 400,
                            'payload' => [
                                'success' => false,
                                'message' => 'Visit not allowed',
                                'code' => 4,
                            ],
                        ];
                    }
                }

                $locker = $this->allocateFreeLocker($customer);

                if ($locker === null) {
                    return [
                        'status' => 409,
                        'payload' => [
                            'success' => false,
                            'message' => 'No free lockers available',
                            'code' => 14,
                        ],
                    ];
                }

                $customerGymService->finished_visits_amount = (int) $customerGymService->finished_visits_amount + 1;
                $customerGymService->save();

                if (! $isPeriodical && (int) $customerGymService->finished_visits_amount >= (int) $gymService->visit_amount) {
                    $customerGymService->expired_at = Carbon::now();
                    $customerGymService->is_active = 0;
                    $customerGymService->save();
                }

                $visit = CustomerVisit::query()->create([
                    'customer_id' => (int) $customer->id,
                    'gym_service_id' => (int) $gymService->id,
                    'start' => Carbon::now(),
                    'locker_number' => $locker['locker_id'],
                    'locker_room_id' => $locker['locker_room_id'],
                    'is_finished' => 0,
                ]);

                return [
                    'status' => 200,
                    'payload' => [
                        'success' => true,
                        'message' => 'Visit started successfully',
                        'code' => 0,
                        'data' => [
                            'visit' => base64_encode(json_encode($visit->toArray())),
                        ],
                    ],
                ];
            });

            return response()->json($result['payload'], $result['status']);
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

            if ($banResponse = $this->denyBannedCustomer($customer)) {
                return $banResponse;
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

    /**
     * @return array{status: int, payload: array<string, mixed>}
     */
    private function startSpecialVisit(Customer $customer, int $gymServiceId): array
    {
        return DB::transaction(function () use ($customer, $gymServiceId) {
            $locker = $this->allocateFreeLocker($customer);

            if ($locker === null) {
                return [
                    'status' => 409,
                    'payload' => [
                        'success' => false,
                        'message' => 'No free lockers available',
                        'code' => 14,
                    ],
                ];
            }

            $visit = CustomerVisit::query()->create([
                'customer_id' => (int) $customer->id,
                'gym_service_id' => $gymServiceId,
                'start' => Carbon::now(),
                'locker_number' => $locker['locker_id'],
                'locker_room_id' => $locker['locker_room_id'],
                'is_finished' => 0,
            ]);

            return [
                'status' => 200,
                'payload' => [
                    'success' => true,
                    'message' => 'Visit started successfully',
                    'code' => 0,
                    'data' => [
                        'visit' => base64_encode(json_encode($visit->toArray())),
                    ],
                ],
            ];
        });
    }

    /**
     * @return array{locker_room_id: int, locker_id: int}|null
     */
    private function allocateFreeLocker(Customer $customer): ?array
    {
        $lockerRooms = LockerRoom::query()
            ->where('sex', $customer->sex)
            ->where('is_staff', 0)
            ->where('is_active', 1)
            ->get();

        foreach ($lockerRooms as $room) {
            $lockerRoomItem = LockerRoomItem::query()
                ->where('locker_room_id', (int) $room->id)
                ->where('is_free', 1)
                ->lockForUpdate()
                ->first();

            if ($lockerRoomItem) {
                $lockerRoomItem->is_free = 0;
                $lockerRoomItem->save();

                return [
                    'locker_room_id' => (int) $room->id,
                    'locker_id' => (int) $lockerRoomItem->locker_number,
                ];
            }
        }

        return null;
    }
}

