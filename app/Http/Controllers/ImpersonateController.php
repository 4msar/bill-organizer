<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

final class ImpersonateController extends Controller
{
    /**
     * Take impersonation of the given user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function take(Request $request, User $user)
    {
        /**
         * @var \App\Models\User $authUser
         */
        $authUser = $request->user();

        if (! $authUser->canImpersonate($user)) {
            abort(403, 'You do not have permission to impersonate this user.');
        }

        $authUser->impersonate($user);

        $redirectTo = config('laravel-impersonate.take_redirect_to', '/');

        if ($redirectTo === 'back') {
            return redirect()->back()->with('success', 'You are now impersonating '.$user->name.'.');
        }

        return redirect($redirectTo)->with('success', 'You are now impersonating '.$user->name.'.');
    }

    /**
     * Leave impersonation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leave(Request $request)
    {
        /**
         * @var \App\Models\User $authUser
         */
        $authUser = $request->user();

        if (! $authUser->isImpersonated()) {
            abort(403, 'You are not impersonating any user.');
        }

        $authUser->leaveImpersonation();

        $redirectTo = config('laravel-impersonate.leave_redirect_to', '/');

        if ($redirectTo === 'back') {
            return redirect()->back()->with('success', 'You have stopped impersonating.');
        }

        return redirect($redirectTo)->with('success', 'You have stopped impersonating.');
    }
}
