<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ChecksCustomerBan;
use App\Models\Customer;
use App\Services\CustomerPurchaseService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ChecksCustomerBan;

    public function create(Request $request, CustomerPurchaseService $customerPurchaseService)
    {
        $data = $request->validate([
            'telegram_id' => ['nullable', 'integer'],
            'service_id' => ['required', 'integer'],
        ]);

        try {
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

            $result = $customerPurchaseService->createPaymentLink(
                $customer,
                (int) $data['service_id'],
            );

            if (! $result['success']) {
                $code = match ($result['message']) {
                    'Service not found' => 3,
                    'Customer already has this service' => 2,
                    default => 1,
                };

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'code' => $code,
                ], $result['httpStatus']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'code' => 0,
                'orderReference' => $result['orderReference'],
                'url' => $result['url'],
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
