<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\GymService;
use App\Models\PaymentOrder;
use App\Services\WayForPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class WayForPayController extends Controller
{
    public function purchase(Request $request, WayForPayService $wayForPay)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
        ]);

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

        $alreadyActive = CustomerGymService::query()
            ->where('customer_id', $customer->id)
            ->where('gym_service_id', $service->id)
            ->where('is_active', 1)
            ->first();

        if ($alreadyActive) {
            return response()->json([
                'success' => false,
                'message' => 'Customer already has this service',
                'code' => 2,
            ], 400);
        }

        $amount = (float) $service->price;
        $currency = (string) config('services.wayforpay.currency', 'UAH');

        $order = PaymentOrder::query()->create([
            'order_reference' => 'tmp',
            'customer_id' => $customer->id,
            'gym_service_id' => $service->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'created',
        ]);

        $orderReference = 'gym_'.$order->id.'_'.time();
        $order->order_reference = $orderReference;
        $order->save();

        $orderDate = time();
        $returnUrl = (string) config('services.wayforpay.return_url');
        $serviceUrl = (string) config('services.wayforpay.service_url');

        $payload = $wayForPay->buildPurchaseRequest(
            orderReference: $orderReference,
            orderDateUnix: $orderDate,
            amount: $amount,
            currency: $currency,
            productNames: [(string) $service->name],
            productCounts: [1],
            productPrices: [$amount],
            returnUrl: $returnUrl !== '' ? $returnUrl : null,
            serviceUrl: $serviceUrl !== '' ? $serviceUrl : null,
            language: (string) config('services.wayforpay.language', 'UA'),
        );

        $order->provider_payload = [
            'purchase_request' => $payload,
        ];
        $order->save();

        // Для мобильного/бота удобнее "offline": вернет JSON { url: "..." }
        try {
            $resp = Http::asForm()
                ->timeout(15)
                ->post('https://secure.wayforpay.com/pay?behavior=offline', $payload);

            if ($resp->successful()) {
                $json = $resp->json();
                if (is_array($json) && isset($json['url']) && is_string($json['url'])) {
                    return response()->json([
                        'success' => true,
                        'code' => 0,
                        'orderReference' => $orderReference,
                        'url' => $json['url'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // fallback ниже
        }

        // Fallback: фронт/бот может сделать POST формой на /pay самостоятельно
        return response()->json([
            'success' => true,
            'code' => 0,
            'orderReference' => $orderReference,
            'action' => 'https://secure.wayforpay.com/pay',
            'method' => 'POST',
            'fields' => $payload,
        ]);
    }

    public function callback(Request $request, WayForPayService $wayForPay)
    {
        $payloadRaw = $request->getContent();
        $payload = json_decode($payloadRaw, true);

        if (! is_array($payload)) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        if (! $wayForPay->isValidCallbackSignature($payload)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $orderReference = (string) ($payload['orderReference'] ?? '');
        if ($orderReference === '') {
            return response()->json(['error' => 'Missing orderReference'], 400);
        }

        /** @var PaymentOrder|null $order */
        $order = PaymentOrder::query()->where('order_reference', $orderReference)->first();
        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $transactionStatus = (string) ($payload['transactionStatus'] ?? 'unknown');

        $order->provider_payload = array_merge((array) ($order->provider_payload ?? []), [
            'callback' => $payload,
        ]);

        if (strcasecmp($transactionStatus, 'Approved') === 0) {
            $order->status = 'approved';

            // Уже есть активная услуга — повторно не создаём.
            $existsActive = CustomerGymService::query()
                ->where('customer_id', $order->customer_id)
                ->where('gym_service_id', $order->gym_service_id)
                ->where('is_active', 1)
                ->first();

            if (! $existsActive && $order->customer_id && $order->gym_service_id) {
                $service = GymService::query()->where('id', $order->gym_service_id)->first();
                if ($service) {
                    $expiredAt = null;
                    if ($service->is_periodical) {
                        $expiredAt = Carbon::now()->addDays((int) $service->day_amount);
                    }

                    // Заказ мог быть создан через OrderController с «ожиданием оплаты» (is_active = 0).
                    $pending = CustomerGymService::query()
                        ->where('customer_id', $order->customer_id)
                        ->where('gym_service_id', $order->gym_service_id)
                        ->where('is_active', 0)
                        ->orderByDesc('id')
                        ->first();

                    if ($pending) {
                        $pending->is_active = true;
                        if ($expiredAt !== null) {
                            $pending->expired_at = $expiredAt;
                        }
                        $pending->save();
                    } else {
                        CustomerGymService::query()->create([
                            'customer_id' => $order->customer_id,
                            'gym_service_id' => $order->gym_service_id,
                            'created_at' => Carbon::now(),
                            'expired_at' => $expiredAt,
                            'is_active' => 1,
                        ]);
                    }
                }
            }
        } else {
            $order->status = 'declined';
        }

        $order->save();

        $time = time();
        $status = 'accept';
        $signature = $wayForPay->signCallbackResponse($orderReference, $status, $time);

        return response()->json([
            'orderReference' => $orderReference,
            'status' => $status,
            'time' => $time,
            'signature' => $signature,
        ]);
    }
}

