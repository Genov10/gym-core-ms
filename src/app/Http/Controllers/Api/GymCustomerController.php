<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GymService;
use App\Models\CustomerGymService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GymCustomerController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        try {
            $customer = Customer::query()->firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name' => $data['name'] ?? null,
                    'lastname' => $data['lastname'] ?? null,
                    'username' => $data['username'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'telegram_id' => $data['telegram_id'] ?? null,
                    'created_at' => Carbon::now(),
                    'email' => $data['email'] ?? null,
                    'is_num_verified' => false,
                ]
            );

            if (! $customer->wasRecentlyCreated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer with this phone already exists',
                    'data' => $customer,
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer registered successfully',
                'data' => $customer,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCustomerGymServices(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
        ]);
        
        $customer = Customer::query()->where('telegram_id', (int) $data['telegram_id'])->first();
        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'code' => 4,
            ], 404);
        }
        
        $customerGymServices = CustomerGymService::query()
            ->where('customer_id', (int) $customer->id)
            ->where('is_active', 1)
            ->with('gymService:id,name')
            ->get();

        $services = [];
        foreach ($customerGymServices as $customerGymService) {
            if (! $customerGymService->gymService) {
                // Orphaned row: the related service was deleted; skip to avoid 500.
                continue;
            }

            $services[] = [
                'id' => $customerGymService->gymService->id,
                'name' => $customerGymService->gymService->name,
            ];
        }
        return response()->json([
            'success' => true,
            'message' => 'Customer gym services fetched successfully',
            'code' => 0,
            'data' => $services,
        ], 200);
    }
}

