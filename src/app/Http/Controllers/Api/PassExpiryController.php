<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerGymService;
use App\Services\AdminBroadcastNotifier;
use App\Services\CustomerPurchaseService;
use App\Services\PassExpiryWebhookNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class PassExpiryController extends Controller
{
    public function checkPassesForOneDay(PassExpiryWebhookNotifier $notifier): JsonResponse
    {
        return $this->checkPassesExpiringInDays(
            daysFromNow: 1,
            configKey: 'one_day_pass',
            message: 'One-day pass check completed',
            notifier: $notifier,
        );
    }

    public function checkPassesForThreeDays(PassExpiryWebhookNotifier $notifier): JsonResponse
    {
        return $this->checkPassesExpiringInDays(
            daysFromNow: 3,
            configKey: 'three_days_pass',
            message: 'Three-day pass check completed',
            notifier: $notifier,
        );
    }

    public function checkPassesForFiveDays(
        AdminBroadcastNotifier $broadcastNotifier,
        CustomerPurchaseService $purchaseService,
    ): JsonResponse {
        $targetDay = Carbon::now()->addDays(5);
        $dayStart = $targetDay->copy()->startOfDay();
        $dayEnd = $targetDay->copy()->endOfDay();

        $subscriptions = CustomerGymService::query()
            ->where('is_active', true)
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$dayStart, $dayEnd])
            ->whereHas('gymService', function ($query): void {
                $query->where('is_periodical', true);
            })
            ->with([
                'customer:id,telegram_id,is_banned,is_student,is_military_member',
                'gymService:id,is_periodical,visit_amount,sale_for_next',
            ])
            ->get();

        $withDiscount = [];
        $withoutDiscount = [];

        foreach ($subscriptions as $subscription) {
            $customer = $subscription->customer;
            if (! $customer || $customer->is_banned || ! $customer->telegram_id) {
                continue;
            }

            $telegramId = (int) $customer->telegram_id;
            if ($telegramId <= 0) {
                continue;
            }

            $service = $subscription->gymService;
            if (! $service) {
                $withoutDiscount[] = $telegramId;
                continue;
            }

            if ($purchaseService->isEligibleForNextPurchaseDiscount($customer, $service)) {
                $withDiscount[] = $telegramId;
            } else {
                $withoutDiscount[] = $telegramId;
            }
        }

        $withDiscount = array_values(array_unique($withDiscount));
        $withoutDiscount = array_values(array_unique($withoutDiscount));
        // Если один telegram попал в обе группы — приоритет сообщению со скидкой.
        $withoutDiscount = array_values(array_diff($withoutDiscount, $withDiscount));

        $discountUntil = Carbon::today()->addDays(2)->format('d.m');
        $messageWithoutDiscount = 'Ваш абонемент закінчується через 5 днів';
        $messageWithDiscount = "Ваш абонемент закінчується через 5 днів\nВи можете отримати знижку на наступний абонемент до {$discountUntil}";

        $sentWithout = $withoutDiscount !== []
            ? $broadcastNotifier->send($withoutDiscount, $messageWithoutDiscount)
            : false;
        $sentWith = $withDiscount !== []
            ? $broadcastNotifier->send($withDiscount, $messageWithDiscount)
            : false;

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => 'Five-day pass check completed',
            'data' => [
                'expires_on' => $dayStart->toDateString(),
                'days_ahead' => 5,
                'discount_until' => $discountUntil,
                'without_discount' => [
                    'telegram_ids' => $withoutDiscount,
                    'count' => count($withoutDiscount),
                    'notification_sent' => $sentWithout,
                    'message' => $messageWithoutDiscount,
                ],
                'with_discount' => [
                    'telegram_ids' => $withDiscount,
                    'count' => count($withDiscount),
                    'notification_sent' => $sentWith,
                    'message' => $messageWithDiscount,
                ],
            ],
        ]);
    }

    private function checkPassesExpiringInDays(
        int $daysFromNow,
        string $configKey,
        string $message,
        PassExpiryWebhookNotifier $notifier,
    ): JsonResponse {
        $targetDay = Carbon::now()->addDays($daysFromNow);
        $dayStart = $targetDay->copy()->startOfDay();
        $dayEnd = $targetDay->copy()->endOfDay();

        $subscriptions = CustomerGymService::query()
            ->where('is_active', true)
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$dayStart, $dayEnd])
            ->whereHas('gymService', function ($query): void {
                $query->where('is_periodical', true);
            })
            ->with('customer:id,telegram_id,is_banned')
            ->get();

        $telegramIds = $subscriptions
            ->map(fn (CustomerGymService $row) => $row->customer)
            ->filter(fn ($customer) => $customer && ! $customer->is_banned && $customer->telegram_id)
            ->map(fn ($customer) => (int) $customer->telegram_id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $sent = $notifier->notify($telegramIds, $configKey);

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => $message,
            'data' => [
                'telegram_ids' => $telegramIds,
                'count' => count($telegramIds),
                'notification_sent' => $sent,
                'expires_on' => $dayStart->toDateString(),
                'days_ahead' => $daysFromNow,
            ],
        ]);
    }
}
