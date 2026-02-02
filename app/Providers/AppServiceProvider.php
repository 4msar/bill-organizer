<?php

namespace App\Providers;

use BackedEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerMacros();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        // get the request class instance to check for headers
        $useHttps = $request->server('HTTP_X_FORWARDED_PROTO', 'http') === 'https' || app()->isProduction();

        URL::forceScheme($useHttps ? 'https' : 'http');
        URL::forceHttps($useHttps);
    }

    private function registerMacros(): void
    {
        Carbon::macro('startOfPeriod', function (mixed $period): Carbon {
            if ($period instanceof BackedEnum) {
                $period = $period->value;
            }

            return match ($period) {
                'weekly' => $this->startOfWeek(),
                'monthly' => $this->startOfMonth(),
                'yearly' => $this->startOfYear(),
                default => throw new \InvalidArgumentException("Invalid period: $period"),
            };
        });

        Carbon::macro('endOfPeriod', function (mixed $period): Carbon {
            if ($period instanceof BackedEnum) {
                $period = $period->value;
            }
            return match ($period) {
                'weekly' => $this->endOfWeek(),
                'monthly' => $this->endOfMonth(),
                'yearly' => $this->endOfYear(),
                default => throw new \InvalidArgumentException("Invalid period: $period"),
            };
        });
    }
}
