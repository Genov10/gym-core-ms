<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Services\SubscriptionFreezeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionFreezeController extends Controller
{
    use ChecksCustomerBan;

    public function preview(Request $request, SubscriptionFreezeService $freezeService): JsonResponse
    {
        $resolved = $this->resolveActivePeriodicalSubscription($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [$customer, $subscription, $service] = $resolved;
        $available = $freezeService->getAvailableFreezeDays($subscription, $service);
        $canFreeze = $freezeService->canFreeze($subscription, $service);

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => 'Freeze preview fetched successfully',
            'data' => [
                'service_name' => $service->name,
                'is_periodical' => (bool) $service->is_periodical,
                'freeze_day_amount' => (int) $service->freeze_day_amount,
                'freeze_days_used' => (int) $subscription->freeze_days_used,
                'freeze_days_available' => $available,
                'is_freeze_active' => $freezeService->isFreezeActive($subscription),
                'can_be_frosen' => $canFreeze,
                'date_from' => $subscription->created_at?->toDateString(),
                'date_to' => $subscription->expired_at?->toDateString(),
                'rules' => [
                    'Максимум днів заморозки для цього абонементу: '.(int) $service->freeze_day_amount,
                    'Використано днів заморозки: '.(int) $subscription->freeze_days_used,
                    'Доступно днів заморозки: '.$available,
                    'Заморозка завершиться при старті наступного тренування або коли вичерпаються дні заморозки',
                    'При закінченні заморозки абонемент продовжиться на кількість днів заморозки',
                ],
            ],
        ]);
    }

    public function confirm(Request $request, SubscriptionFreezeService $freezeService): JsonResponse
    {
        $resolved = $this->resolveActivePeriodicalSubscription($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        [, $subscription] = $resolved;
        $result = $freezeService->startFreeze($subscription);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'code' => $result['code'],
            ], 400);
        }

        $subscription->refresh();

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => 'Subscription freeze started successfully',
            'data' => [
                'freeze_start' => $subscription->freeze_start?->toDateString(),
                'freeze_days_available' => $freezeService->getAvailableFreezeDays($subscription),
            ],
        ]);
    }

    public function finishExpired(SubscriptionFreezeService $freezeService): JsonResponse
    {
        $closed = $freezeService->endExpiredFreezes();

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => 'Expired freezes processed',
            'data' => [
                'closed_count' => $closed,
            ],
        ]);
    }

    /**
     * @return array{0: Customer, 1: CustomerGymService, 2: \App\Models\GymService}|JsonResponse
     */
    private function resolveActivePeriodicalSubscription(Request $request): array|JsonResponse
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

        $subscription = CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', (int) $data['service_id'])
            ->where('is_active', 1)
            ->with('gymService')
            ->orderByDesc('id')
            ->first();

        if (! $subscription || ! $subscription->gymService) {
            return response()->json([
                'success' => false,
                'message' => 'Active customer service not found',
                'code' => 4,
            ], 404);
        }

        if (! $subscription->gymService->is_periodical) {
            return response()->json([
                'success' => false,
                'message' => 'Freeze is available only for periodical subscriptions',
                'code' => 4,
            ], 400);
        }

        return [$customer, $subscription, $subscription->gymService];
    }
}
