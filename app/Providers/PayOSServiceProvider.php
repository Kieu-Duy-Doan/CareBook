<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PayOS\PayOS;

class PayOSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PayOS::class, function ($app) {
            return new PayOS(
                config('payos.client_id'),
                config('payos.api_key'),
                config('payos.checksum_key')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
