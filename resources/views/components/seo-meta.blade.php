@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'robots' => null,
    'canonical' => null,
])

@php
    $siteTitle = config('seo.title');
    $separator = config('seo.title_separator');

    // Build the full title
    $fullTitle = $title ? $title . $separator . $siteTitle : $siteTitle;

    // Get description
    $metaDescription = $description ?? config('seo.description');

    // Get keywords
    $metaKeywords = $keywords ?? config('seo.keywords');

    // Get image URL
    $ogImage = $image ?? config('seo.og_image');
    $ogImageUrl = str_starts_with($ogImage, 'http') ? $ogImage : url($ogImage);

    // Get current URL
    $currentUrl = $url ?? $canonical ?? request()->url();

    // Get canonical URL
    $canonicalUrl = $canonical ?? $currentUrl;

    // Get robots directive
    $robotsDirective = $robots ?? config('seo.robots');

    // Get locale
    $locale = config('seo.locale');

    // Get Twitter handle
    $twitterHandle = config('seo.twitter_handle');
    $twitterCardType = config('seo.twitter_card_type');
@endphp

{{-- Page Title --}}
<title>{{ $fullTitle }}</title>

{{-- Primary Meta Tags --}}
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="robots" content="{{ $robotsDirective }}">
<meta name="author" content="{{ config('seo.organization_name') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $currentUrl }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:locale" content="{{ $locale }}">
<meta property="og:site_name" content="{{ $siteTitle }}">

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $twitterCardType }}">
<meta name="twitter:site" content="{{ $twitterHandle }}">
<meta name="twitter:url" content="{{ $currentUrl }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
