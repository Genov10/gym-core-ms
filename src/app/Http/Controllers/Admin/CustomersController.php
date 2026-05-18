<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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
