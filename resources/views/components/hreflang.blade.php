@props([
    'url' => null,
])

@php
    $currentUrl = $url ?? request()->url();
    $baseUrl = config('app.url');

    // High-value AdSense countries for international targeting
    $hreflangLocales = [
        'en' => 'en',           // English (Default)
        'en-GB' => 'en-GB',     // English (UK)
        'en-AU' => 'en-AU',     // English (Australia)
        'en-CA' => 'en-CA',     // English (Canada)
        'de' => 'de',           // German (Germany/Austria/Switzerland)
        'fr' => 'fr',           // French (France/Belgium)
        'nl' => 'nl',           // Dutch (Netherlands/Belgium)
    ];
@endphp

{{-- x-default for users with no language preference --}}
<link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">

{{-- Hreflang tags for high-value international markets --}}
@foreach($hreflangLocales as $code => $locale)
<link rel="alternate" hreflang="{{ $locale }}" href="{{ $currentUrl }}">
@endforeach
