<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGymService;
use App\Models\CustomerVisit;
use App\Models\PaymentOrder;
use App\Services\CustomerPurchaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $query = Customer::query()
            ->where(function (Builder $q): void {
                $q->where('is_staff', false)->orWhereNull('is_staff');
            })
            ->orderByDesc('id');

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
            'listMode' => 'customers',
            'listTitle' => 'Клиенты',
            'listSubtitle' => 'Список зарегистрированных клиентов (is_staff = false)',
            'listBaseUrl' => url('/admin/customers'),
            'emptyLabel' => 'клиентов',
            'profileBaseUrl' => url('/admin/customers'),
        ]);
    }

    public function show(Customer $customer, string $listMode = 'customers')
    {
        if ($listMode === 'customers' && $customer->is_staff) {
            return redirect('/admin/staff/'.$customer->id);
        }
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

        $listMode = $customer->is_staff ? 'staff' : $listMode;

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
            'listMode' => $listMode,
            'listBaseUrl' => $listMode === 'staff' ? url('/admin/staff') : url('/admin/customers'),
            'profileBaseUrl' => $listMode === 'staff' ? url('/admin/staff') : url('/admin/customers'),
        ]);
    }

    public function toggleBan(Customer $customer)
    {
        $customer->is_banned = ! $customer->is_banned;
        $customer->save();

        $message = $customer->is_banned
            ? 'Клиент заблокирован.'
            : 'Блокировка снята.';

        return redirect($this->profileUrl($customer))->with('status', $message);
    }

    public function toggleStaff(Customer $customer)
    {
        $customer->is_staff = ! (bool) $customer->is_staff;
        $customer->save();

        if ($customer->is_staff) {
            return redirect('/admin/staff/'.$customer->id)->with('status', 'Добавлен в персонал.');
        }

        return redirect('/admin/customers/'.$customer->id)->with('status', 'Убран из персонала.');
    }

    public function updateFlags(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'is_military_member' => ['nullable', 'boolean'],
            'is_student' => ['nullable', 'boolean'],
        ]);

        $customer->update([
            'is_military_member' => (bool) ($data['is_military_member'] ?? false),
            'is_student' => (bool) ($data['is_student'] ?? false),
        ]);

        return redirect($this->profileUrl($customer))->with('status', 'Скидочные статусы сохранены.');
    }

    public function freezeSubscription(Request $request, Customer $customer, CustomerGymService $subscription)
    {
        if ((int) $subscription->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $subscription->load('gymService:id,name,is_periodical');

        if (! $subscription->is_active || ! $subscription->gymService?->is_periodical) {
            return redirect($this->profileUrl($customer))
                ->withErrors(['freeze' => 'Заморозка доступна только для активных периодических абонементов.']);
        }

        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $base = $subscription->expired_at !== null
            ? Carbon::parse($subscription->expired_at)
            : Carbon::now();

        $subscription->expired_at = $base->copy()->addDays((int) $data['days']);
        $subscription->save();

        return redirect($this->profileUrl($customer))->with(
            'status',
            'Абонемент «'.($subscription->gymService?->name ?? '#'.$subscription->id).'» продлён на '.$data['days'].' дн. Действует до '.$subscription->expired_at->format('Y-m-d H:i').'.'
        );
    }

    public function sellableServices(Customer $customer, CustomerPurchaseService $customerPurchaseService)
    {
        return response()->json([
            'data' => $customerPurchaseService->listPricedServices($customer, excludeOwned: true),
        ]);
    }

    public function createPaymentLink(Request $request, Customer $customer, CustomerPurchaseService $customerPurchaseService)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer'],
        ]);

        $result = $customerPurchaseService->createPaymentLink(
            $customer,
            (int) $data['service_id'],
            skipBanCheck: true,
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['httpStatus']);
        }

        return response()->json([
            'success' => true,
            'url' => $result['url'],
            'orderReference' => $result['orderReference'],
        ]);
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

    private function profileUrl(Customer $customer): string
    {
        return $customer->is_staff
            ? '/admin/staff/'.$customer->id
            : '/admin/customers/'.$customer->id;
    }
}
