<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GymService;
use App\Models\CustomerGymService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function create(Request $request)
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
            $expired_at = null;
            if ($is_periodical) {
                $expired_at = Carbon::now()->addDays($service->day_amount);
            } 
            CustomerGymService::query()->create([
                'customer_id' => $customer->id,
                'gym_service_id' => $service->id,
                'created_at' => Carbon::now(),
                'expired_at' => $expired_at,
                'is_active' => 1,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'code' => 0,
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

