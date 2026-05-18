<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = (string) $request->query('status', '');

        $query = PaymentOrder::query()
            ->with([
                'customer:id,name,lastname,phone,telegram_id,email',
                'gymService:id,name',
            ])
            ->orderByDesc('id');

        if ($statusFilter !== '' && in_array($statusFilter, ['created', 'approved', 'declined', 'refunded'], true)) {
            $query->where('status', $statusFilter);
        }

        $orders = $query->get();

        $stats = [
            'total_orders' => PaymentOrder::query()->count(),
            'approved_count' => PaymentOrder::query()->where('status', 'approved')->count(),
            'approved_sum' => (float) PaymentOrder::query()->where('status', 'approved')->sum('amount'),
            'created_count' => PaymentOrder::query()->where('status', 'created')->count(),
            'declined_count' => PaymentOrder::query()->where('status', 'declined')->count(),
        ];

        return view('admin.sales.index', [
            'orders' => $orders,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
        ]);
    }
}
