<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\GymService;
use App\Models\PaymentOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class SubscriptionExtendService
{
    public const EXTEND_DAYS = 14;

    public function __construct(
        private readonly WayForPayService $wayForPay,
    ) {}

    public function canExtend(Customer $customer, CustomerGymService $subscription, ?GymService $service = null): bool
    {
        $service ??= $subscription->gymService;

        if (! $service || (int) $service->can_be_extended <= 0) {
            return false;
        }

        if (! $subscription->is_active || (bool) $subscription->was_extended) {
            return false;
        }

        if ($this->hasUnstartedDuplicate($customer, $service, $subscription)) {
            return false;
        }

        return true;
    }

    public function hasUnstartedDuplicate(
        Customer $customer,
        GymService $service,
        CustomerGymService $subscription,
    ): bool {
        return CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', (int) $service->id)
            ->where('id', '!=', (int) $subscription->id)
            ->whereNull('created_at')
            ->whereNull('expired_at')
            ->exists();
    }

    /**
     * @return array{
     *   success: true,
     *   price: float,
     *   extend_days: int,
     *   url: string,
     *   orderReference: string
     * }|array{success: false, message: string, code: int, httpStatus: int}
     */
    public function createExtendPayment(Customer $customer, CustomerGymService $subscription): array
    {
        $subscription->loadMissing('gymService');
        $service = $subscription->gymService;

        if (! $service || ! $this->canExtend($customer, $subscription, $service)) {
            return [
                'success' => false,
                'message' => 'Extension not available',
                'code' => 4,
                'httpStatus' => 400,
            ];
        }

        if ($subscription->expired_at === null) {
            return [
                'success' => false,
                'message' => 'Subscription period is not started',
                'code' => 4,
                'httpStatus' => 400,
            ];
        }

        $amount = (float) $service->can_be_extended;
        $currency = (string) config('services.wayforpay.currency', 'UAH');
        $extendDays = self::EXTEND_DAYS;

        $order = PaymentOrder::query()->create([
            'order_reference' => 'tmp',
            'customer_id' => $customer->id,
            'gym_service_id' => $service->id,
            'customer_gym_service_id' => $subscription->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'created',
            'purpose' => PaymentOrder::PURPOSE_EXTEND,
        ]);

        $orderReference = 'gym_ext_'.$order->id.'_'.time();
        $order->order_reference = $orderReference;
        $order->save();

        $returnUrl = (string) config('services.wayforpay.return_url');
        $serviceUrl = $this->extendConfirmUrl();

        $productName = 'Продлення: '.$service->name.' (+'.$extendDays.' днів)';

        $payload = $this->wayForPay->buildPurchaseRequest(
            orderReference: $orderReference,
            orderDateUnix: time(),
            amount: $amount,
            currency: $currency,
            productNames: [$productName],
            productCounts: [1],
            productPrices: [$amount],
            returnUrl: $returnUrl !== '' ? $returnUrl : null,
            serviceUrl: $serviceUrl !== '' ? $serviceUrl : null,
            language: (string) config('services.wayforpay.language', 'UA'),
        );

        $order->provider_payload = [
            'purchase_request' => $payload,
            'extend_days' => $extendDays,
        ];
        $order->save();

        try {
            $resp = Http::asForm()
                ->timeout(15)
                ->post('https://secure.wayforpay.com/pay?behavior=offline', $payload);

            if ($resp->successful()) {
                $json = $resp->json();
                if (is_array($json) && isset($json['url']) && is_string($json['url'])) {
                    return [
                        'success' => true,
                        'price' => $amount,
                        'extend_days' => $extendDays,
                        'url' => $json['url'],
                        'orderReference' => $orderReference,
                    ];
                }
            }
        } catch (\Throwable $e) {
            //
        }

        return [
            'success' => false,
            'message' => 'Failed to generate payment link from WayForPay',
            'code' => 5,
            'httpStatus' => 502,
        ];
    }

    /**
     * Apply paid extension to the linked customer_gym_service.
     *
     * @return array{success: true, days: int}|array{success: false, message: string}
     */
    public function applyPaidExtension(PaymentOrder $order): array
    {
        if ($order->purpose !== PaymentOrder::PURPOSE_EXTEND) {
            return [
                'success' => false,
                'message' => 'Order is not an extension',
            ];
        }

        $subscriptionId = (int) ($order->customer_gym_service_id ?? 0);
        if ($subscriptionId <= 0) {
            return [
                'success' => false,
                'message' => 'Subscription not linked to order',
            ];
        }

        $subscription = CustomerGymService::query()
            ->where('id', $subscriptionId)
            ->where('customer_id', (int) $order->customer_id)
            ->first();

        if (! $subscription) {
            return [
                'success' => false,
                'message' => 'Subscription not found',
            ];
        }

        $days = (int) (($order->provider_payload['extend_days'] ?? null) ?: self::EXTEND_DAYS);

        if ($subscription->expired_at === null) {
            return [
                'success' => false,
                'message' => 'Subscription period is not started',
            ];
        }

        if ($subscription->was_extended) {
            return [
                'success' => true,
                'days' => $days,
            ];
        }

        $subscription->expired_at = $subscription->expired_at->copy()->addDays($days);
        $subscription->was_extended = true;
        $subscription->extend_timestamp = Carbon::now();
        $subscription->save();

        return [
            'success' => true,
            'days' => $days,
        ];
    }

    private function extendConfirmUrl(): string
    {
        $configured = (string) config('services.wayforpay.extend_service_url', '');
        if ($configured !== '') {
            return $configured;
        }

        $appUrl = rtrim((string) config('app.url', ''), '/');

        return $appUrl !== '' ? $appUrl.'/api/wayforpay/extend-confirm' : '';
    }
}
