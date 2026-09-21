<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\PaymentOrder;
use App\Services\PaymentResultNotifier;
use App\Services\SubscriptionExtendService;
use App\Services\WayForPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionExtendController extends Controller
{
    use ChecksCustomerBan;

    public function create(Request $request, SubscriptionExtendService $extendService): JsonResponse
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

        $result = $extendService->createExtendPayment($customer, $subscription);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'code' => $result['code'],
            ], $result['httpStatus']);
        }

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => 'Extension payment link created successfully',
            'data' => [
                'price' => $result['price'],
                'extend_days' => $result['extend_days'],
                'url' => $result['url'],
                'orderReference' => $result['orderReference'],
            ],
        ]);
    }

    public function confirm(
        Request $request,
        WayForPayService $wayForPay,
        SubscriptionExtendService $extendService,
        PaymentResultNotifier $paymentResultNotifier,
    ): JsonResponse {
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

        if ($order->purpose !== PaymentOrder::PURPOSE_EXTEND) {
            return response()->json(['error' => 'Order is not an extension'], 400);
        }

        $transactionStatus = (string) ($payload['transactionStatus'] ?? 'unknown');
        $order->provider_payload = array_merge((array) ($order->provider_payload ?? []), [
            'callback' => $payload,
        ]);

        $paymentSuccess = strcasecmp($transactionStatus, 'Approved') === 0;
        $customer = $order->customer;

        if ($paymentSuccess && $customer?->is_banned) {
            $paymentSuccess = false;
        }

        if ($paymentSuccess) {
            if ($order->status !== 'approved') {
                $apply = $extendService->applyPaidExtension($order);
                if (! $apply['success']) {
                    $order->status = 'declined';
                    $order->save();

                    return response()->json(['error' => $apply['message']], 400);
                }
                $order->status = 'approved';
            }
        } else {
            $order->status = 'declined';
        }

        $order->save();

        if ($customer && $customer->telegram_id) {
            $serviceName = $order->gymService?->name ?? '';
            $paymentResultNotifier->notify(
                telegramId: (int) $customer->telegram_id,
                serviceName: $serviceName !== '' ? 'Продлення: '.$serviceName : 'Продлення абонементу',
                success: $paymentSuccess,
            );
        }

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
