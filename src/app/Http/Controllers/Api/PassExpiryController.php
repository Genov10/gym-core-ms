<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerGymService;
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
