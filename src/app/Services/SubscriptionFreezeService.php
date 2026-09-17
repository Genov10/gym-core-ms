<?php

namespace App\Services;

use App\Models\CustomerGymService;
use App\Models\GymService;
use Illuminate\Support\Carbon;

class SubscriptionFreezeService
{
    public function getAvailableFreezeDays(CustomerGymService $subscription, ?GymService $service = null): int
    {
        $service ??= $subscription->gymService;

        if (! $service || ! $service->is_periodical) {
            return 0;
        }

        $max = max(0, (int) $service->freeze_day_amount);
        $used = max(0, (int) $subscription->freeze_days_used);

        return max(0, $max - $used);
    }

    public function isFreezeActive(CustomerGymService $subscription): bool
    {
        return $subscription->freeze_start !== null && $subscription->freeze_end === null;
    }

    public function canFreeze(CustomerGymService $subscription, ?GymService $service = null): bool
    {
        $service ??= $subscription->gymService;

        if (! $subscription->is_active || ! $service?->is_periodical) {
            return false;
        }

        if ($this->isFreezeActive($subscription)) {
            return false;
        }

        if ($subscription->created_at === null || $subscription->expired_at === null) {
            return false;
        }

        return $this->getAvailableFreezeDays($subscription, $service) > 0;
    }

    /**
     * @return array{success: true}|array{success: false, message: string, code: int}
     */
    public function startFreeze(CustomerGymService $subscription): array
    {
        $subscription->loadMissing('gymService');
        $service = $subscription->gymService;

        if (! $this->canFreeze($subscription, $service)) {
            return [
                'success' => false,
                'message' => 'Freeze not available',
                'code' => 4,
            ];
        }

        $subscription->freeze_start = Carbon::now();
        $subscription->freeze_end = null;
        $subscription->save();

        return ['success' => true];
    }

    /**
     * Ends an active freeze, accumulates used days and extends expired_at.
     *
     * @return array{success: true, days: int}|array{success: false, message: string, code: int}
     */
    public function endFreeze(CustomerGymService $subscription, ?Carbon $endedAt = null): array
    {
        $subscription->loadMissing('gymService');

        if (! $this->isFreezeActive($subscription)) {
            return [
                'success' => false,
                'message' => 'No active freeze',
                'code' => 4,
            ];
        }

        $endedAt ??= Carbon::now();
        $available = $this->getAvailableFreezeDays($subscription);
        $days = $subscription->freeze_start->copy()->startOfDay()
            ->diffInDays($endedAt->copy()->startOfDay());
        $days = max(0, min((int) $days, $available));

        $subscription->freeze_days_used = (int) $subscription->freeze_days_used + $days;
        $subscription->freeze_end = $endedAt;

        if ($subscription->expired_at !== null && $days > 0) {
            $subscription->expired_at = $subscription->expired_at->copy()->addDays($days);
        }

        $subscription->save();

        return [
            'success' => true,
            'days' => $days,
        ];
    }

    /**
     * Auto-end freezes that reached their remaining allowance.
     *
     * @return int Number of freezes closed
     */
    public function endExpiredFreezes(): int
    {
        $subscriptions = CustomerGymService::query()
            ->where('is_active', true)
            ->whereNotNull('freeze_start')
            ->whereNull('freeze_end')
            ->with('gymService:id,is_periodical,freeze_day_amount')
            ->get();

        $closed = 0;
        $today = Carbon::today();

        foreach ($subscriptions as $subscription) {
            $available = $this->getAvailableFreezeDays($subscription);
            if ($available <= 0) {
                $this->endFreeze($subscription, $today);
                $closed++;
                continue;
            }

            $daysPassed = $subscription->freeze_start->copy()->startOfDay()->diffInDays($today);
            if ($daysPassed >= $available) {
                $this->endFreeze($subscription, $today);
                $closed++;
            }
        }

        return $closed;
    }
}
