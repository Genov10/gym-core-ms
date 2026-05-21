<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\Customer;

class CustomerProvider extends ServiceProvider
{
    

    public function getCustomerIdByTelegramId(int $telegramId): int
    {
        return Customer::query()->where('telegram_id', $telegramId)->first()->id;
    }

        
}
