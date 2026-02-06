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
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
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
            $exception,
            $request
        ) {
            // handle /api requests with JSON responses, and web requests with Inertia error pages
            if ($request->is('api/*')) {
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
