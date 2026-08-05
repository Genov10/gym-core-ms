<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\Customer;
use App\Models\CustomerVisit;

class CustomerProvider extends ServiceProvider
{
    

    public function getCustomerIdByTelegramId(int $telegramId): int
    {
        return Customer::query()->where('telegram_id', $telegramId)->first()->id;
    }

    public function isGuestVisitAvailable(int $customerId): int
    {
        
        $customerVisits = CustomerVisit::query()
        ->where('customer_id', (int) $customer->id)
        ->get();

        if (empty($customerVisits)) {
            return 1;
        }
        return 0;
    
    }

        
}
