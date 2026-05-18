<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $statusFilter = (string) $request->query('status', '');

        $dateFrom = $this->parseDateInput((string) $request->query('date_from', $today), $today);
        $dateTo = $this->parseDateInput((string) $request->query('date_to', $today), $today);

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [
                Carbon::parse($dateTo)->startOfDay(),
                Carbon::parse($dateFrom)->endOfDay(),
            ];
            [$dateFrom, $dateTo] = [$from->toDateString(), $to->toDateString()];
        }

        $scoped = fn (): Builder => PaymentOrder::query()->whereBetween('created_at', [$from, $to]);

        $query = $scoped()
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
            'total_orders' => $scoped()->count(),
            'approved_count' => $scoped()->where('status', 'approved')->count(),
            'approved_sum' => (float) $scoped()->where('status', 'approved')->sum('amount'),
            'created_count' => $scoped()->where('status', 'created')->count(),
            'declined_count' => $scoped()->where('status', 'declined')->count(),
        ];

        return view('admin.sales.index', [
            'orders' => $orders,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function parseDateInput(string $value, string $fallback): string
    {
        if ($value === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
