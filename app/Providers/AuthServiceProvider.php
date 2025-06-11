<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Category::class    => \App\Policies\CategoryPolicy::class,
        \App\Models\Product::class     => \App\Policies\ProductPolicy::class,
        \App\Models\Client::class      => \App\Policies\ClientPolicy::class,
        \App\Models\Supply::class      => \App\Policies\SupplyPolicy::class,
        \App\Models\Invoice::class     => \App\Policies\InvoicePolicy::class,
        \App\Models\Transaction::class => \App\Policies\TransactionPolicy::class,
        \App\Models\User::class        => \App\Policies\UserPolicy::class,
        \App\Models\Role::class        => \App\Policies\RolePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
