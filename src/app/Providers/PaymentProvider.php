<?php

namespace App\Providers;

use App\Services\WayForPayService;
use Illuminate\Support\ServiceProvider;

class PaymentProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WayForPayService::class, function () {
            return new WayForPayService(
                merchantAccount: (string) config('services.wayforpay.merchant_account'),
                secretKey: (string) config('services.wayforpay.secret_key'),
                merchantDomainName: (string) config('services.wayforpay.merchant_domain_name'),
            );
        });
    }
}
