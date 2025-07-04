<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user?->append('metas'),
            ],
            'team' => fn () => $this->getTeam($request),
            'ziggy' => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'notifications' => $this->getNotifications($request),
            'flash' => [
                'success' => $request->session()->pull('success'),
                'error' => $request->session()->pull('error'),
                'warning' => $request->session()->pull('warning'),
                'info' => $request->session()->pull('info'),
            ],
        ];
    }

    /**
     * Get the notifications for the user.
     */
    protected function getNotifications(Request $request): array
    {
        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        if (! $user) {
            return [];
        }

        return [
            'unread' => $user->unreadNotifications->count(),
            'last' => $user->notifications()
                ->latest()
                ->unread()
                ->first(),
        ];
    }

    /**
     * Get the team of login user
     */
    protected function getTeam(Request $request): array
    {
        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        return [
            'current' => $user?->activeTeam,
            'items' => $user?->teams ?? [],
        ];
    }
}
