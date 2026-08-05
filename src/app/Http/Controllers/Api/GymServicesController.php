<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerPurchaseService;
use Illuminate\Http\Request;

class GymServicesController extends Controller
{
    public function index(Request $request, CustomerPurchaseService $customerPurchaseService)
    {
        $data = $request->validate([
            'telegram_id' => ['nullable', 'integer'],
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

        return response()->json([
            'data' => $customerPurchaseService->listPricedServices($customer),
        ]);
    }
}

