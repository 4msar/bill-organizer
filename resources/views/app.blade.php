<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta Tags --}}
    <meta name="description"
        content="Bill Organizer - Efficiently manage, track, and organize all your bills and payments in one secure place. Never miss a payment again with our intuitive bill management system.">
    <meta name="keywords"
        content="bill organizer, bill tracking, payment management, bill reminder, expense tracker, financial organization, budget management, due date tracker">
    <meta name="author" content="Bill Organizer">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#3b82f6">
    <meta name="msapplication-TileColor" content="#3b82f6">

    {{-- Structured Data for SEO --}}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "{{ config('app.name', 'Bill Organizer') }}",
            "description": "Efficiently manage, track, and organize all your bills and payments in one secure place. Never miss a payment again with our intuitive bill management system.",
            "url": "{{ config('app.url', 'https://bills.msar.me') }}",
            "applicationCategory": "FinanceApplication",
            "operatingSystem": "Web Browser",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "USD"
            }
        }
    </script>

    {{-- Open Graph Meta Tags for Social Media --}}
    <meta property="og:title"
        content="{{ config('app.name', 'Bill Organizer') }}">
    <meta property="og:description"
        content="Efficiently manage, track, and organize all your bills and payments in one secure place. Never miss a payment again.">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="{{ config('app.url', 'http://localhost') }}">
    <meta property="og:site_name"
        content="{{ config('app.name', 'Bill Organizer') }}">
    <meta property="og:locale"
        content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:image" content="{{ asset('logo.svg') }}">
    <meta property="og:image:alt"
        content="{{ config('app.name', 'Bill Organizer') }} Logo">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title"
        content="{{ config('app.name', 'Bill Organizer') }}">
    <meta name="twitter:description"
        content="Efficiently manage, track, and organize all your bills and payments in one secure place.">
    <meta name="twitter:image" content="{{ asset('logo.svg') }}">
    <meta name="twitter:image:alt"
        content="{{ config('app.name', 'Bill Organizer') }} Logo">

    {{-- Canonical URL --}}
    <link rel="canonical"
        href="{{ config('app.url', 'https://bills.msar.me/') }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? 'system' }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia(
                    '(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap"
        rel="stylesheet">

    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
