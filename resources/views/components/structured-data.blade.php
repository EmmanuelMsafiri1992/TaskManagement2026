@props([
    'type' => 'WebSite',
    'breadcrumbs' => null,
])

@php
    $organizationName = config('seo.organization_name');
    $siteUrl = config('app.url');
    $siteName = config('seo.title');
    $description = config('seo.description');
    $logoUrl = url(config('seo.og_image'));
@endphp

@if($type === 'WebSite' || $type === 'Organization')
{{-- Organization Schema --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "{{ $organizationName }}",
    "url": "{{ $siteUrl }}",
    "logo": "{{ $logoUrl }}",
    "description": "{{ $description }}",
    "sameAs": []
}
</script>

{{-- WebSite Schema with SearchAction --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ $siteName }}",
    "url": "{{ $siteUrl }}",
    "description": "{{ $description }}",
    "publisher": {
        "@type": "Organization",
        "name": "{{ $organizationName }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $logoUrl }}"
        }
    },
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ $siteUrl }}/search?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endif

@if($breadcrumbs && count($breadcrumbs) > 0)
{{-- BreadcrumbList Schema --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($breadcrumbs as $index => $breadcrumb)
        {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "name": "{{ $breadcrumb['name'] }}",
            "item": "{{ $breadcrumb['url'] }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- SoftwareApplication Schema (optional for task management) --}}
@if($type === 'WebSite')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "{{ $siteName }}",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web Browser",
    "description": "{{ $description }}",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
@endif
