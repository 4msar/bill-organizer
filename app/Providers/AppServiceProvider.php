<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        $useHttps = request()->server('HTTP_X_FORWARDED_PROTO', 'http') === 'https' || app()->isProduction();
        URL::forceScheme($useHttps ? 'https' : 'http');
    }
}
