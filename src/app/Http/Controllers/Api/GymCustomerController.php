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
        try {
            $customer = Customer::query()->create([
                'name' => $request->input('name'),
                'sex' => $request->input('sex'),
                'telegram_id' => $request->input('telegram_id'),
                'created_at' => Carbon::now(),
                'phone' => $request->input('phone'),
                'email' =>  !empty($request->input('email')) ? $request->input('email') : null,
                'is_num_verified' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer registration failed',
                'error' => $e->getMessage(),
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Customer registered successfully',
            'data' => $customer,
        ]);
    }
}

