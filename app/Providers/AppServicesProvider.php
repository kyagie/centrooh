<?php

namespace App\Providers;

use App\Services\Billboards\BillboardAssignmentService;
use Illuminate\Support\ServiceProvider;

class AppServicesProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BillboardAssignmentService::class, function ($app) {
            return new BillboardAssignmentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
