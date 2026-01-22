<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Title
    |--------------------------------------------------------------------------
    |
    | The default site title used in the <title> tag and Open Graph tags.
    |
    */

    'title' => env('SEO_TITLE', 'TaskManagement - Professional Task & Project Management'),

    /*
    |--------------------------------------------------------------------------
    | Title Separator
    |--------------------------------------------------------------------------
    |
    | The separator used between page title and site title.
    |
    */

    'title_separator' => ' | ',

    /*
    |--------------------------------------------------------------------------
    | Site Description
    |--------------------------------------------------------------------------
    |
    | The default meta description for search engines.
    |
    */

    'description' => env('SEO_DESCRIPTION', 'Streamline your workflow with TaskManagement - a powerful task and project management solution. Organize tasks, collaborate with teams, and boost productivity.'),

    /*
    |--------------------------------------------------------------------------
    | Keywords
    |--------------------------------------------------------------------------
    |
    | Default meta keywords for the site.
    |
    */

    'keywords' => env('SEO_KEYWORDS', 'task management, project management, productivity, team collaboration, workflow, todo list, project tracking, task tracker'),

    /*
    |--------------------------------------------------------------------------
    | Default OG Image
    |--------------------------------------------------------------------------
    |
    | The default Open Graph image path (relative to public folder).
    |
    */

    'og_image' => env('SEO_OG_IMAGE', '/images/og-image.png'),

    /*
    |--------------------------------------------------------------------------
    | Twitter Handle
    |--------------------------------------------------------------------------
    |
    | Your Twitter handle for Twitter Cards.
    |
    */

    'twitter_handle' => env('SEO_TWITTER_HANDLE', '@taskmanagement'),

    /*
    |--------------------------------------------------------------------------
    | Twitter Card Type
    |--------------------------------------------------------------------------
    |
    | The type of Twitter Card to use (summary, summary_large_image, app, player).
    |
    */

    'twitter_card_type' => 'summary_large_image',

    /*
    |--------------------------------------------------------------------------
    | Site Type
    |--------------------------------------------------------------------------
    |
    | The Schema.org type for the website (WebSite, Organization, etc.).
    |
    */

    'site_type' => 'WebSite',

    /*
    |--------------------------------------------------------------------------
    | Organization Name
    |--------------------------------------------------------------------------
    |
    | The organization name for structured data.
    |
    */

    'organization_name' => env('SEO_ORGANIZATION_NAME', 'TaskManagement'),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale for Open Graph tags.
    |
    */

    'locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales for Hreflang
    |--------------------------------------------------------------------------
    |
    | Target locales for international SEO (high-value AdSense countries).
    |
    */

    'hreflang_locales' => [
        'en' => 'English (Default)',
        'en-GB' => 'English (UK)',
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'de' => 'German (Germany/Austria/Switzerland)',
        'fr' => 'French (France/Belgium)',
        'nl' => 'Dutch (Netherlands/Belgium)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots Default Directive
    |--------------------------------------------------------------------------
    |
    | Default robots meta directive.
    |
    */

    'robots' => 'index, follow',

    /*
    |--------------------------------------------------------------------------
    | Sitemap Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for sitemap generation.
    |
    */

    'sitemap' => [
        'homepage_priority' => '1.0',
        'homepage_changefreq' => 'daily',
        'default_priority' => '0.8',
        'default_changefreq' => 'weekly',
    ],

];
