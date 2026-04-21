<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GymService;

class GymServicesController extends Controller
{
    public function index()
    {
        $services = GymService::query()
            ->orderBy('id')
            ->get(['id', 'name', 'price', 'is_active', 'is_periodical', 'day_amount', 'visit_amount']);

        return response()->json([
            'data' => $services,
        ]);
    }
}

