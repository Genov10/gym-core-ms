<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GymService;
use App\Models\Customer;
use Illuminate\Http\Request;

class GymServicesController extends Controller
{
    public function index( Request $request)
    {

        $data = $request->validate([
            'telegram_id' => ['nullable', 'integer'],
        ]);

        $services = GymService::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'price', 'description', 'is_active', 'is_periodical', 'day_amount', 'visit_amount', 'sales_default', 'sales_military_member', 'sales_student']);


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
        $finalServicesArray = [];
        foreach ($services as $service) {
            $amount = (int) $service->price;
            $currency = (string) config('services.wayforpay.currency', 'UAH');

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

            $finalServicesArray[] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $amount,
                'sale_from' => (int) $service->price,
                'description' => $service->description,
            ];
        }
        return response()->json([
            'data' => $finalServicesArray,
        ]);
    }
}

