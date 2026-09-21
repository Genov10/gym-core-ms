<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\GymService;
use App\Models\PaymentOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CustomerPurchaseService
{
    public function __construct(
        private readonly WayForPayService $wayForPay,
    ) {}

    /**
     * @return list<array{id: int, name: string, price: int, sale_from: int, description: string|null}>
     */
    public function listPricedServices(Customer $customer, bool $excludeOwned = false): array
    {
        $services = GymService::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'price',
                'description',
                'sales_default',
                'sales_military_member',
                'sales_student',
                'sale_for_next',
            ]);

        $activeServiceIds = $excludeOwned
            ? CustomerGymService::query()
                ->where('customer_id', $customer->id)
                ->where('is_active', 1)
                ->pluck('gym_service_id')
                ->all()
            : [];

        $result = [];

        foreach ($services as $service) {
            if (in_array($service->id, $activeServiceIds, true)) {
                continue;
            }

            $amount = $this->calculatePrice($service, $customer);

            $result[] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $amount,
                'sale_from' => (int) $service->price,
                'description' => $service->description,
            ];
        }

        return $result;
    }

    public function calculatePrice(GymService $service, Customer $customer): int
    {
        $amount = (int) $service->price;

        $sale = (int) ($service->sales_default ?? 0);
        if ($customer->is_military_member > 0) {
            $sale = (int) $service->sales_military_member;
        }
        if ($customer->is_student > 0) {
            $sale = (int) $service->sales_student;
        }

        if ($this->isEligibleForNextPurchaseDiscount($customer, $service)) {
            $sale = max($sale, (int) $service->sale_for_next);
        }

        if ($sale > 0) {
            $amount = (int) ceil((float) $service->price / 100 * (100 - $sale));
        }

        return $amount;
    }

    /**
     * Скидка на следующий такой же абонемент: sale_for_next > 0 и до конца текущего ≤ 3 дней.
     */
    public function isEligibleForNextPurchaseDiscount(Customer $customer, GymService $service): bool
    {
        if ((int) $service->sale_for_next <= 0) {
            return false;
        }

        if ($this->hasUnstartedSubscription($customer, (int) $service->id)) {
            return false;
        }

        $current = $this->findStartedActiveSubscription($customer, $service);
        if (! $current || $current->expired_at === null) {
            return false;
        }

        $expiresAt = $current->expired_at->copy()->startOfDay();
        $today = Carbon::today();

        if ($expiresAt->lt($today)) {
            return false;
        }

        return $today->diffInDays($expiresAt) <= 3;
    }

    /**
     * Есть оплаченный, но ещё не начатый абонемент этой услуги.
     */
    public function hasUnstartedSubscription(Customer $customer, int $serviceId): bool
    {
        return CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', $serviceId)
            ->where('is_active', 1)
            ->whereNull('created_at')
            ->whereNull('expired_at')
            ->exists();
    }

    /**
     * Начатый и ещё не истёкший абонемент (кейс 2 — его берём на визит).
     */
    public function findStartedActiveSubscription(Customer $customer, GymService $service): ?CustomerGymService
    {
        $now = Carbon::now();
        $isPeriodical = (bool) $service->is_periodical;

        $query = CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', (int) $service->id)
            ->where('is_active', 1)
            ->whereNotNull('created_at');

        if ($isPeriodical) {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '>=', $now)
                ->orderBy('expired_at');
        } else {
            $visitAmount = (int) $service->visit_amount;
            $query->whereRaw('COALESCE(finished_visits_amount, 0) < ?', [$visitAmount])
                ->orderBy('id');
        }

        return $query->first();
    }

    /**
     * Неначатый активный абонемент (кейс 1 — стартуем его на первом визите).
     */
    public function findUnstartedSubscription(Customer $customer, GymService $service): ?CustomerGymService
    {
        return CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('gym_service_id', (int) $service->id)
            ->where('is_active', 1)
            ->whereNull('created_at')
            ->whereNull('expired_at')
            ->orderBy('id')
            ->first();
    }

    /**
     * Для визита: сначала текущий начатый, иначе неначатый.
     */
    public function resolveSubscriptionForVisit(Customer $customer, GymService $service): ?CustomerGymService
    {
        return $this->findStartedActiveSubscription($customer, $service)
            ?? $this->findUnstartedSubscription($customer, $service);
    }

    /**
     * @return array{success: true, url: string, orderReference: string}|array{success: false, message: string, httpStatus: int}
     */
    public function createPaymentLink(Customer $customer, int $serviceId, bool $skipBanCheck = false): array
    {
        if (! $skipBanCheck && $customer->is_banned) {
            return [
                'success' => false,
                'message' => 'Customer is banned',
                'httpStatus' => 403,
            ];
        }

        $service = GymService::query()
            ->where('id', $serviceId)
            ->first();

        if (! $service) {
            return [
                'success' => false,
                'message' => 'Service not found',
                'httpStatus' => 404,
            ];
        }

        if ($this->hasUnstartedSubscription($customer, (int) $service->id)) {
            return [
                'success' => false,
                'message' => 'Customer already has an unstarted subscription for this service',
                'httpStatus' => 400,
            ];
        }

        $customerGymService = CustomerGymService::query()->create([
            'customer_id' => $customer->id,
            'gym_service_id' => $service->id,
            'created_at' => null,
            'expired_at' => null,
            'is_active' => 0,
            'purchase_date' => Carbon::now(),
        ]);

        $amount = (float) $this->calculatePrice($service, $customer);
        $currency = (string) config('services.wayforpay.currency', 'UAH');

        $paymentOrder = PaymentOrder::query()->create([
            'order_reference' => 'tmp',
            'customer_id' => $customer->id,
            'gym_service_id' => $service->id,
            'customer_gym_service_id' => $customerGymService->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'created',
            'purpose' => PaymentOrder::PURPOSE_PURCHASE,
        ]);

        $orderReference = 'gym_'.$paymentOrder->id.'_'.time();
        $paymentOrder->order_reference = $orderReference;
        $paymentOrder->save();

        $returnUrl = (string) config('services.wayforpay.return_url');
        $serviceUrl = (string) config('services.wayforpay.service_url');

        $payload = $this->wayForPay->buildPurchaseRequest(
            orderReference: $orderReference,
            orderDateUnix: time(),
            amount: $amount,
            currency: $currency,
            productNames: [(string) $service->name],
            productCounts: [1],
            productPrices: [$amount],
            returnUrl: $returnUrl !== '' ? $returnUrl : null,
            serviceUrl: $serviceUrl !== '' ? $serviceUrl : null,
            language: (string) config('services.wayforpay.language', 'UA'),
        );

        $paymentOrder->provider_payload = ['purchase_request' => $payload];
        $paymentOrder->save();

        try {
            $resp = Http::asForm()
                ->timeout(15)
                ->post('https://secure.wayforpay.com/pay?behavior=offline', $payload);

            if ($resp->successful()) {
                $json = $resp->json();
                if (is_array($json) && isset($json['url']) && is_string($json['url'])) {
                    return [
                        'success' => true,
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
            'httpStatus' => 502,
        ];
    }
}
