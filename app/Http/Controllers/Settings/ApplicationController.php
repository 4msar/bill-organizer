<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ApplicationController extends Controller
{
    /**
     * Show the application settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Application');
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string',
            'currency_symbol' => 'required|string',
        ]);

        $user = $request->user();

        foreach ($validated as $key => $value) {
            $user->setMeta($key, $value);
        }

        return back();
    }
}
