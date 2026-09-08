<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGymService;
use App\Models\GymService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $typeFilter = (string) $request->query('type', '');
        if (! in_array($typeFilter, ['periodical', 'package'], true)) {
            $typeFilter = '';
        }

        $serviceIdRaw = trim((string) $request->query('service_id', ''));
        $serviceId = ctype_digit($serviceIdRaw) ? (int) $serviceIdRaw : 0;

        $visitsFromRaw = trim((string) $request->query('visits_from', ''));
        $visitsToRaw = trim((string) $request->query('visits_to', ''));
        $visitsFrom = preg_match('/^\d+$/', $visitsFromRaw) ? (int) $visitsFromRaw : null;
        $visitsTo = preg_match('/^\d+$/', $visitsToRaw) ? (int) $visitsToRaw : null;

        if ($visitsFrom !== null && $visitsTo !== null && $visitsFrom > $visitsTo) {
            [$visitsFrom, $visitsTo] = [$visitsTo, $visitsFrom];
        }

        $filters = [
            'customer' => trim((string) $request->query('customer', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'type' => $typeFilter,
            'service_id' => $serviceId,
            'visits_from' => $visitsFrom,
            'visits_to' => $visitsTo,
            'is_active' => $request->has('filtered')
                ? $request->boolean('is_active')
                : true,
        ];

        $dateFrom = $this->parseOptionalDate($filters['date_from']);
        $dateTo = $this->parseOptionalDate($filters['date_to']);

        $query = CustomerGymService::query()
            ->with([
                'customer:id,name,lastname,phone,telegram_id,email,username',
                'gymService:id,name,price,is_periodical,visit_amount,day_amount',
            ])
            ->orderByDesc('id');

        if ($filters['is_active']) {
            $query->where('is_active', true);
        }

        if ($dateFrom !== null) {
            $query->whereNotNull('created_at')
                ->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo !== null) {
            $query->whereNotNull('expired_at')
                ->where('expired_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if ($filters['customer'] !== '') {
            $this->applyCustomerFilter($query, $filters['customer']);
        }

        if ($filters['type'] === 'periodical') {
            $query->whereHas('gymService', fn (Builder $q) => $q->where('is_periodical', true));
        } elseif ($filters['type'] === 'package') {
            $query->whereHas('gymService', fn (Builder $q) => $q->where('is_periodical', false));
        }

        if ($filters['service_id'] > 0) {
            $query->where('gym_service_id', $filters['service_id']);
        }

        if ($filters['visits_from'] !== null) {
            $query->where('finished_visits_amount', '>=', $filters['visits_from']);
        }

        if ($filters['visits_to'] !== null) {
            $query->where('finished_visits_amount', '<=', $filters['visits_to']);
        }

        $subscriptions = $query->get();

        $services = GymService::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active', 'is_periodical']);

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'foundCount' => $subscriptions->count(),
            'services' => $services,
            'filters' => [
                'customer' => $filters['customer'],
                'date_from' => $dateFrom ?? '',
                'date_to' => $dateTo ?? '',
                'type' => $filters['type'],
                'service_id' => $filters['service_id'],
                'visits_from' => $filters['visits_from'],
                'visits_to' => $filters['visits_to'],
                'is_active' => $filters['is_active'],
            ],
        ]);
    }

    private function applyCustomerFilter(Builder $query, string $value): void
    {
        $username = ltrim($value, '@');
        $pattern = '%'.$this->escapeLike($username).'%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->whereHas('customer', function (Builder $q) use ($username, $value, $pattern, $operator): void {
            $q->where(function (Builder $inner) use ($username, $value, $pattern, $operator): void {
                $inner->where('name', $operator, $pattern)
                    ->orWhere('lastname', $operator, $pattern)
                    ->orWhere('phone', $operator, $pattern)
                    ->orWhere('email', $operator, $pattern)
                    ->orWhere('username', $operator, $pattern);

                if (preg_match('/^\d+$/', $value)) {
                    $inner->orWhere('id', (int) $value)
                        ->orWhere('telegram_id', (int) $value);
                }

                $telegramPattern = '%'.$this->escapeLike($value).'%';
                $driver = $inner->getConnection()->getDriverName();

                if ($driver === 'pgsql') {
                    $inner->orWhereRaw('telegram_id::text ILIKE ?', [$telegramPattern]);
                } else {
                    $inner->orWhereRaw('CAST(telegram_id AS TEXT) LIKE ?', [$telegramPattern]);
                }
            });
        });
    }

    private function parseOptionalDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
