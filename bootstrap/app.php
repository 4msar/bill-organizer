<?php

use App\Http\Middleware\EnsureJsonResponse;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\UserCanAccessFeature;
use App\Http\Middleware\UserHasTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        /**
         * Send upcoming bill notifications to users.
         *
         * In production, consider running it less frequently (e.g., every six hours)
         * to avoid spamming users with notifications.
         */
        $sendUpcomingNotification = $schedule->command('app:send-upcoming-bill-notifications');

        if (app()->isProduction()) {
            // In production, run every six hours to avoid spamming users
            $sendUpcomingNotification->everySixHours();
        } else {
            // In development, run every minute to test notifications
            $sendUpcomingNotification->everyMinute();
        }
        $sendUpcomingNotification->runInBackground();

        /**
         * Update bill statuses based on recurrence period and transactions.
         *
         * Run daily at 12:05 AM to ensure statuses are up-to-date.
         */
        $schedule->command('bills:update-statuses')
            ->dailyAt('00:05')
            ->runInBackground();

        /**
         * Create transactions for bills with auto transaction enabled.
         *
         * Run daily at 12:10 AM.
         */
        $schedule->command('bills:create-auto-transactions')
            ->dailyAt('00:10')
            ->runInBackground();

        /**
         * Clear expired API tokens to maintain security and performance.
         *
         * Run daily at 1:00 AM to clean up expired tokens.
         */
        $schedule->command('sanctum:prune-expired')
            ->dailyAt('01:00')
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Encrypt cookies except appearance and sidebar_state
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Trust proxies for HTTPS detection
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->api(append: [
            EnsureJsonResponse::class,
        ]);

        // Web middleware group
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Custom middleware aliases
        $middleware->alias([
            'team' => UserHasTeam::class,
            'feature' => UserCanAccessFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (
            Response $response,
            Throwable $exception,
            Request $request
        ) {
            // handle /api requests with JSON responses, and web requests with Inertia error pages
            if ($request->is('api/*')) {

                if ($exception instanceof ValidationException) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'The given data was invalid.',
                        'errors' => $exception->errors(),
                    ], 422);
                }

                return response()->json([
                    'status' => $response->getStatusCode(),
                    'message' => $exception->getMessage() ?? 'Something went wrong.',
                ], $response->getStatusCode());
            }

            /** @var Illuminate\Http\Response $response */
            $response = $response;
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'error' => 'The page expired, please try again.',
                ]);
            }

            if (
                $response->getStatusCode() >= 500 &&
                app()->environment('local', 'testing')
            ) {
                return $response;
            }

            if (in_array($response->getStatusCode(), [403, 404, 500, 503])) {
                return inertia('errors/404', [
                    'status' => $response->getStatusCode(),
                    'message' => $exception->getMessage() ?? 'Something went wrong.',
                ])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
