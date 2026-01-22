<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- SEO Meta Tags --}}
        <x-seo-meta
            :title="$seoTitle ?? null"
            :description="$seoDescription ?? null"
            :keywords="$seoKeywords ?? null"
            :image="$seoImage ?? null"
            :robots="$seoRobots ?? 'noindex, nofollow'"
        />

        {{-- Hreflang Tags for International Targeting --}}
        <x-hreflang />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Structured Data --}}
        <x-structured-data type="WebSite" />
    </head>
    <body class="antialiased">
        {{ $slot }}
    </body>
</html>
