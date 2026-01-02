<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

final class LegalController extends Controller
{
    /**
     * Display the terms of service page.
     */
    public function terms()
    {
        return Inertia::render('legal/Terms');
    }

    /**
     * Display the privacy policy page.
     */
    public function privacy()
    {
        return Inertia::render('legal/Privacy');
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return Inertia::render('legal/Contact');
    }
}
