<?php

namespace App\Providers;

use App\Contracts\CircuitBreakerInterface;
use App\Contracts\TransactionInterface;
use App\Services\CircuitBreaker;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->when(TransactionService::class)
            ->needs(CircuitBreakerInterface::class)
            ->give(function () {
                return new CircuitBreaker('transaction_service');
            });

        $this->app->bind(TransactionInterface::class, TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
