<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\CustomerVisit;
use App\Models\PaymentOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'name' => trim((string) $request->query('name', '')),
            'lastname' => trim((string) $request->query('lastname', '')),
            'phone' => trim((string) $request->query('phone', '')),
            'email' => trim((string) $request->query('email', '')),
            'identity' => trim((string) $request->query('identity', '')),
        ];

        $query = Customer::query()->orderByDesc('id');

        $this->applyLikeFilter($query, 'name', $filters['name']);
        $this->applyLikeFilter($query, 'lastname', $filters['lastname']);
        $this->applyLikeFilter($query, 'phone', $filters['phone']);
        $this->applyLikeFilter($query, 'email', $filters['email']);

        if ($filters['identity'] !== '') {
            $this->applyIdentityFilter($query, $filters['identity']);
        }

        $customers = $query->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $filters,
        ]);
    }

    public function show(Customer $customer)
    {
        $activeVisit = CustomerVisit::query()
            ->where('customer_id', $customer->id)
            ->where('is_finished', false)
            ->with([
                'gymService:id,name',
                'lockerRoom:id,name',
            ])
            ->first();

        $ordersQuery = PaymentOrder::query()->where('customer_id', $customer->id);

        $purchaseStats = [
            'total' => (int) $ordersQuery->clone()->count(),
            'approved_count' => (int) $ordersQuery->clone()->where('status', 'approved')->count(),
            'approved_sum' => (float) $ordersQuery->clone()->where('status', 'approved')->sum('amount'),
            'created_count' => (int) $ordersQuery->clone()->where('status', 'created')->count(),
            'declined_count' => (int) $ordersQuery->clone()->where('status', 'declined')->count(),
        ];

        $orders = PaymentOrder::query()
            ->where('customer_id', $customer->id)
            ->with('gymService:id,name,price')
            ->orderByDesc('id')
            ->get();

        $subscriptions = CustomerGymService::query()
            ->where('customer_id', $customer->id)
            ->with('gymService:id,name,price,is_periodical,visit_amount,day_amount')
            ->orderByDesc('id')
            ->get();

        $visits = CustomerVisit::query()
            ->where('customer_id', $customer->id)
            ->with([
                'gymService:id,name',
                'lockerRoom:id,name',
            ])
            ->orderByDesc('start')
            ->orderByDesc('id')
            ->get();

        $subscriptionStats = [
            'total' => $subscriptions->count(),
            'active' => $subscriptions->where('is_active', true)->count(),
        ];

        $telegramUrl = $customer->username
            ? 'https://t.me/'.ltrim($customer->username, '@')
            : null;

        $displayName = trim(($customer->name ?? '').' '.($customer->lastname ?? '')) ?: 'Клиент #'.$customer->id;

        return view('admin.customers.show', [
            'customer' => $customer,
            'displayName' => $displayName,
            'activeVisit' => $activeVisit,
            'purchaseStats' => $purchaseStats,
            'subscriptionStats' => $subscriptionStats,
            'orders' => $orders,
            'subscriptions' => $subscriptions,
            'visits' => $visits,
            'telegramUrl' => $telegramUrl,
        ]);
    }

    public function toggleBan(Customer $customer)
    {
        $customer->is_banned = ! $customer->is_banned;
        $customer->save();

        $message = $customer->is_banned
            ? 'Клиент заблокирован.'
            : 'Блокировка снята.';

        return redirect('/admin/customers/'.$customer->id)->with('status', $message);
    }

    private function applyLikeFilter(Builder $query, string $column, string $value): void
    {
        if ($value === '') {
            return;
        }

        $pattern = '%'.$this->escapeLike($value).'%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->where($column, $operator, $pattern);
    }

    private function applyIdentityFilter(Builder $query, string $value): void
    {
        $username = ltrim($value, '@');
        $pattern = '%'.$this->escapeLike($username).'%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->where(function (Builder $q) use ($username, $value, $pattern, $operator): void {
            $q->where('username', $operator, $pattern);

            if (preg_match('/^\d+$/', $value)) {
                $q->orWhere('telegram_id', (int) $value);
            }

            $telegramPattern = '%'.$this->escapeLike($value).'%';
            $driver = $q->getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                $q->orWhereRaw('telegram_id::text ILIKE ?', [$telegramPattern]);
            } else {
                $q->orWhereRaw('CAST(telegram_id AS TEXT) LIKE ?', [$telegramPattern]);
            }
        });
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
