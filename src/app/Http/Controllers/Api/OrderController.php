<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Models\Customer;
use App\Models\GymService;
use App\Models\CustomerGymService;
use App\Models\PaymentOrder;
use App\Services\WayForPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;


class OrderController extends Controller
{
    use ChecksCustomerBan;

    public function create(Request $request, WayForPayService $wayForPay)
    {
        $data = $request->validate([
            'telegram_id' => ['nullable', 'integer'],
            'service_id' => ['required', 'integer'],
        ]);

        try {
            $service = GymService::query()
                ->where('id', (int) $data['service_id'])
                ->first();

            if (! $service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found',
                    'code' => 3,
                ], 404);
            }

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

            $customerGymServiceCheck = CustomerGymService::query()->where('customer_id', $customer->id)->where('gym_service_id', $service->id)->where('is_active', 1)->first();
            
            if ($customerGymServiceCheck) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer already has this service',
                    'code' => 2,
                ], 400);
            }

            $is_periodical = false;
            if ($service->is_periodical) {
                $is_periodical = true;
            }
            $created_at = null;
            $expired_at = null;
            // if ($is_periodical) {
            //     $expired_at = Carbon::now()->addDays($service->day_amount);
            // } 
            $customerGymService = CustomerGymService::query()->create([
                'customer_id' => $customer->id,
                'gym_service_id' => $service->id,
                'created_at' => $created_at,
                'expired_at' => $expired_at,
                'is_active' => 0,
                'purchase_date' => Carbon::now(),
            ]);

            $amount = (float) $service->price;
            $currency = (string) config('services.wayforpay.currency', 'UAH');

            $sale = $service->sales_default;
            if ($customer->is_military_member) {
                $sale = $service->sales_military_member;
            }
            if ($customer->is_student) {
                $sale = $service->sales_student;
            }


            if ($sale > 0) {
                $amount = ceil((float) $service->price / 100 * (100 - $sale));
            }
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

            $payload = $wayForPay->buildPurchaseRequest(
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
                        return response()->json([
                            'success' => true,
                            'message' => 'Order created successfully',
                            'code' => 0,
                            'orderReference' => $orderReference,
                            'url' => $json['url'],
                        ], 200);
                    }
                }
            } catch (\Throwable $e) {
                //
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'code' => 0,
                'orderReference' => $orderReference,
                'action' => 'https://secure.wayforpay.com/pay',
                'method' => 'POST',
                'fields' => $payload,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'code' => 1,
                'message' => 'Order create failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

