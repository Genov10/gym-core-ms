<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GymCustomerController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
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
}

