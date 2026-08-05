<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerVisit;
use Illuminate\Support\ServiceProvider;

class CustomerProvider extends ServiceProvider
{
    public static function getCustomerIdByTelegramId(int $telegramId): int
    {
        return Customer::query()->where('telegram_id', $telegramId)->first()->id;
    }

    public static function isGuestVisitAvailable(int $customerId): int
    {
        $hasVisits = CustomerVisit::query()
            ->where('customer_id', $customerId)
            ->exists();

        return $hasVisits ? 0 : 1;
    }
}
