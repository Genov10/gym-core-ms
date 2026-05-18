<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitsController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $finishedFilter = (string) $request->query('finished', '');

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

        $scoped = fn (): Builder => CustomerVisit::query()->whereBetween('start', [$from, $to]);

        $query = $scoped()
            ->with([
                'customer:id,name,lastname,phone,telegram_id',
                'gymService:id,name',
                'lockerRoom:id,name',
            ])
            ->orderByDesc('start')
            ->orderByDesc('id');

        if ($finishedFilter === '1') {
            $query->where('is_finished', true);
        } elseif ($finishedFilter === '0') {
            $query->where('is_finished', false);
        }

        $visits = $query->get();

        $stats = [
            'total' => $scoped()->count(),
            'finished' => $scoped()->where('is_finished', true)->count(),
            'active' => $scoped()->where('is_finished', false)->count(),
            'unique_customers' => (int) $scoped()->whereNotNull('customer_id')->distinct()->count('customer_id'),
        ];

        return view('admin.visits.index', [
            'visits' => $visits,
            'stats' => $stats,
            'finishedFilter' => $finishedFilter,
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
