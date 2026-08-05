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

        $sale = $service->sales_default;
        if ($customer->is_military_member > 0) {
            $sale = $service->sales_military_member;
        }
        if ($customer->is_student > 0) {
            $sale = $service->sales_student;
        }

        if ($sale > 0) {
            $amount = (int) ceil((float) $service->price / 100 * (100 - $sale));
        }

        return $amount;
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

        $alreadyActive = CustomerGymService::query()
            ->where('customer_id', $customer->id)
            ->where('gym_service_id', $service->id)
            ->where('is_active', 1)
            ->exists();

        if ($alreadyActive) {
            return [
                'success' => false,
                'message' => 'Customer already has this service',
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
