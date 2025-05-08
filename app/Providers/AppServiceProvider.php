<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limiter (60 requests per minute)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // OTP request rate limiter (5 requests per minute per phone number)
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('phone_number') ?: $request->ip());
        });

        // Image upload rate limiter (10 uploads per minute per agent)
        // RateLimiter::for('uploads', function (Request $request) {
        //     if ($request->user() && $request->user()->agent) {
        //         return Limit::perMinute(10)->by('agent:' . $request->user()->agent->id);
        //     }
            
        //     return Limit::perMinute(2)->by('ip:' . $request->ip());
        // });

        // Billboard data rate limiter (100 requests per minute per agent)
        // RateLimiter::for('billboards', function (Request $request) {
        //     if ($request->user() && $request->user()->agent) {
        //         return Limit::perMinute(100)->by('agent:' . $request->user()->agent->id);
        //     }
            
        //     return Limit::perMinute(30)->by('ip:' . $request->ip());
        // });
    }
}
