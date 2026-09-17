<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Models\Customer;
use App\Models\GymService;
use App\Models\CustomerGymService;
use App\Services\SubscriptionFreezeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Providers\CustomerProvider;
class GymCustomerController extends Controller
{
    use ChecksCustomerBan;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        try {
            $customer = Customer::query()->firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name' => $data['name'] ?? null,
                    'lastname' => $data['lastname'] ?? null,
                    'username' => $data['username'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'telegram_id' => $data['telegram_id'] ?? null,
                    'created_at' => Carbon::now(),
                    'email' => $data['email'] ?? null,
                    'is_num_verified' => false,
                ]
            );

            if ($banResponse = $this->denyBannedCustomer($customer)) {
                return $banResponse;
            }

            if (! $customer->wasRecentlyCreated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer with this phone already exists',
                    'data' => $customer,
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer registered successfully',
                'data' => $customer,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCustomerGymServices(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
        ]);
        
        $customer = Customer::query()->where('telegram_id', (int) $data['telegram_id'])->first();
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

        $customerGymServices = CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('is_active', 1)
            ->with('gymService:id,name,is_periodical,visit_amount')
            ->get();

        $now = Carbon::now();
        $services = [];
        foreach ($customerGymServices as $customerGymService) {
            $service = $customerGymService->gymService;
            if (! $service) {
                // Orphaned row: the related service was deleted; skip to avoid 500.
                continue;
            }

            // Keep list in sync with startVisit: hide already expired / exhausted passes.
            if ((bool) $service->is_periodical) {
                if ($customerGymService->expired_at && $customerGymService->expired_at->lt($now)) {
                    $customerGymService->is_active = false;
                    $customerGymService->save();
                    continue;
                }
            } else {
                $remaining = (int) $service->visit_amount - (int) $customerGymService->finished_visits_amount;
                if ($remaining <= 0) {
                    $customerGymService->is_active = false;
                    $customerGymService->expired_at ??= $now;
                    $customerGymService->save();
                    continue;
                }
            }

            $services[] = [
                'id' => $service->id,
                'name' => $service->name,
            ];
        }

        $isGuestVisitAvailable = CustomerProvider::isGuestVisitAvailable($customer->id);

        if ($isGuestVisitAvailable == 1) {
            $services[] = [
                'id' => 0,
                'name' => 'Гостьовий візит',
            ];
        }

        $isStaff = $customer->is_staff;
        if ($isStaff) {
            $services[] = [
                'id' => -1,
                'name' => 'Персонал',
            ];
        }
        return response()->json([
            'success' => true,
            'message' => 'Customer gym services fetched successfully',
            'code' => 0,
            'data' => $services,
        ], 200);
    }

    public function getCustomerGymServiceInfo(Request $request, SubscriptionFreezeService $freezeService)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
        ]);

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

        // Guest visit (virtual service id = 0)
        if ($serviceId === 0) {
            if (CustomerProvider::isGuestVisitAvailable((int) $customer->id) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest visit not available',
                    'code' => 4,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer gym service info fetched successfully',
                'code' => 0,
                'data' => [
                    'service_name' => 'Гостьовий візит',
                    'description' => 'Запрошуємо Вас на перше тренування безкоштовно',
                    'date_from' => null,
                    'date_to' => null,
                    'lefted_visits_amount' => null,
                    'can_be_frosen' => false,
                    'can_be_extended' => false,
                    'can_buy_with_discount' => false,
                ],
            ], 200);
        }

        // Staff entry (virtual service id = -1)
        if ($serviceId === -1) {
            if (! $customer->is_staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff visit not available',
                    'code' => 4,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer gym service info fetched successfully',
                'code' => 0,
                'data' => [
                    'service_name' => 'Персонал',
                    'description' => 'Прохід для персоналу',
                    'date_from' => null,
                    'date_to' => null,
                    'lefted_visits_amount' => null,
                    'can_be_frosen' => false,
                    'can_be_extended' => false,
                    'can_buy_with_discount' => false,
                ],
            ], 200);
        }

        $subscription = CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', $serviceId)
            ->where('is_active', 1)
            ->with('gymService:id,name,description,is_periodical,visit_amount,day_amount,freeze_day_amount')
            ->orderByDesc('id')
            ->first();

        if (! $subscription || ! $subscription->gymService) {
            return response()->json([
                'success' => false,
                'message' => 'Active customer service not found',
                'code' => 4,
            ], 404);
        }

        $service = $subscription->gymService;
        $isPeriodical = (bool) $service->is_periodical;
        $now = Carbon::now();

        if ($isPeriodical && $subscription->expired_at && $subscription->expired_at->lt($now)) {
            $subscription->is_active = false;
            $subscription->save();

            return response()->json([
                'success' => false,
                'message' => 'Subscription expired',
                'code' => 4,
            ], 400);
        }

        $leftedVisitsAmount = null;
        if (! $isPeriodical) {
            $leftedVisitsAmount = max(
                0,
                (int) $service->visit_amount - (int) $subscription->finished_visits_amount
            );

            if ($leftedVisitsAmount <= 0) {
                $subscription->is_active = false;
                $subscription->expired_at ??= $now;
                $subscription->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Visit not allowed',
                    'code' => 4,
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer gym service info fetched successfully',
            'code' => 0,
            'data' => [
                'service_name' => $service->name,
                'description' => $service->description,
                'date_from' => $subscription->created_at?->toDateString(),
                'date_to' => $subscription->expired_at?->toDateString(),
                'lefted_visits_amount' => $leftedVisitsAmount,
                'can_be_frosen' => $freezeService->canFreeze($subscription, $service),
                'can_be_extended' => false,
                'can_buy_with_discount' => false,
            ],
        ], 200);
    }
}

