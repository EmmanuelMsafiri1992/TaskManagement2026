<?php

namespace App\Models\V11;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * V11 Post Model - Represents job postings from the nyasajob database
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $country_code
 * @property string $application_url
 * @property string $company_name
 * @property string $company_description
 * @property float $salary_min
 * @property float $salary_max
 * @property string $currency_code
 * @property string $address
 * @property int $city_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $email_verified_at
 */
class Post extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'v11';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'featured' => 'boolean',
        'is_urgent' => 'boolean',
    ];

    /**
     * Base URL for nyasajob site
     */
    protected const NYASAJOB_BASE_URL = 'https://nyasajob.com';

    /**
     * Scope to get recent posts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $hours  Number of hours to look back (default: 24)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours))
                     ->whereNotNull('email_verified_at')
                     ->whereNull('deleted_at')
                     ->whereNull('archived_at')
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get unassigned posts (posts not yet assigned to any user).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $assignedPostIds  IDs of posts already assigned
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnassigned($query, array $assignedPostIds = [])
    {
        if (!empty($assignedPostIds)) {
            $query->whereNotIn('id', $assignedPostIds);
        }
        return $query->whereNotNull('email_verified_at')
                     ->whereNull('deleted_at')
                     ->whereNull('archived_at')
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter posts by country code.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $countryCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Get the country details for this post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * Get the city for this post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Generate slug from title (matching nyasajob's slugify behavior).
     *
     * @return string
     */
    public function getSlug()
    {
        return Str::slug($this->title);
    }

    /**
     * Generate hashId for the post using Hashids library.
     * Matches nyasajob's hashId() function format exactly.
     *
     * @return string
     */
    public function getHashId()
    {
        // Use same parameters as nyasajob: empty salt, min length 11
        $hashids = new Hashids('', 11);
        return $hashids->encode($this->id);
    }

    /**
     * Get the proper nyasajob URL for this post.
     * Format matches nyasajob's UrlGen::post() with MULTI_COUNTRIES_URLS=true
     *
     * @return string
     */
    public function getUrl()
    {
        $slug = $this->getSlug();
        $hashId = $this->getHashId();
        $countryCode = strtolower($this->country_code);

        // Format: https://nyasajob.com/{country_code}/{slug}/{hashId}
        // This matches nyasajob's URL pattern with MULTI_COUNTRIES_URLS=true
        return self::NYASAJOB_BASE_URL . "/{$countryCode}/{$slug}/{$hashId}";
    }

    /**
     * Get formatted description (cleaned for social media).
     *
     * @param  int  $length
     * @return string
     */
    public function getShortDescription($length = 200)
    {
        $description = strip_tags($this->description);
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        $description = preg_replace('/\s+/', ' ', trim($description));

        if (strlen($description) <= $length) {
            return $description;
        }

        // Cut at word boundary
        $description = substr($description, 0, $length);
        $lastSpace = strrpos($description, ' ');
        if ($lastSpace !== false) {
            $description = substr($description, 0, $lastSpace);
        }

        return $description . '...';
    }

    /**
     * Get country name from code.
     *
     * @return string
     */
    public function getCountryName()
    {
        $countries = [
            'MW' => 'Malawi', 'ZA' => 'South Africa', 'KE' => 'Kenya', 'NG' => 'Nigeria',
            'GH' => 'Ghana', 'ZM' => 'Zambia', 'TZ' => 'Tanzania', 'UG' => 'Uganda',
            'ZW' => 'Zimbabwe', 'BW' => 'Botswana', 'NA' => 'Namibia', 'MZ' => 'Mozambique',
            'RW' => 'Rwanda', 'ET' => 'Ethiopia', 'EG' => 'Egypt', 'MA' => 'Morocco',
            'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada',
            'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France', 'IN' => 'India',
            'PH' => 'Philippines', 'AE' => 'UAE', 'SA' => 'Saudi Arabia', 'QA' => 'Qatar',
        ];

        return $countries[$this->country_code] ?? $this->country_code;
    }

    /**
     * Format salary information with currency.
     *
     * @return string|null
     */
    protected function formatSalary()
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        $currency = $this->currency_code ?? 'USD';
        $currencySymbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'MWK' => 'MK',
            'ZAR' => 'R', 'KES' => 'KSh', 'NGN' => '₦', 'INR' => '₹',
            'AED' => 'AED', 'SAR' => 'SAR', 'QAR' => 'QAR', 'CAD' => 'C$',
            'AUD' => 'A$', 'PHP' => '₱', 'GHS' => 'GH₵', 'ZMW' => 'ZK',
        ];
        $symbol = $currencySymbols[$currency] ?? $currency . ' ';

        if ($this->salary_min && $this->salary_max) {
            return "{$symbol}" . number_format($this->salary_min) . " - {$symbol}" . number_format($this->salary_max);
        }

        if ($this->salary_min) {
            return "From {$symbol}" . number_format($this->salary_min);
        }

        return "Up to {$symbol}" . number_format($this->salary_max);
    }

    /**
     * Format job for social media sharing - optimized for engagement.
     *
     * @param  string  $shortUrl
     * @return string
     */
    public function formatForSharing($shortUrl)
    {
        $location = $this->getCountryName();
        $companyInfo = $this->company_name ? " at **{$this->company_name}**" : "";

        // Build engaging social media post
        $lines = [];

        // Eye-catching header with job type indicators
        $urgentBadge = $this->is_urgent ? "🚨 URGENT: " : "";
        $featuredBadge = $this->featured ? "⭐ " : "";
        $lines[] = "{$urgentBadge}{$featuredBadge}🔥 **{$this->title}**{$companyInfo}";
        $lines[] = "";

        // Location with flag emoji
        $flagEmoji = $this->getCountryFlag();
        $lines[] = "📍 {$flagEmoji} {$location}";

        // Salary (major attraction point)
        $salary = $this->formatSalary();
        if ($salary) {
            $lines[] = "💰 {$salary}";
        }

        // Short description
        $lines[] = "";
        $description = $this->getShortDescription(180);
        $lines[] = $description;

        // Call to action
        $lines[] = "";
        $lines[] = "👉 Apply Now: {$shortUrl}";
        $lines[] = "";

        // Hashtags for discoverability
        $hashtags = $this->generateHashtags();
        $lines[] = $hashtags;

        return implode("\n", $lines);
    }

    /**
     * Get country flag emoji.
     *
     * @return string
     */
    protected function getCountryFlag()
    {
        $code = strtoupper($this->country_code);
        if (strlen($code) !== 2) {
            return '🌍';
        }

        // Convert country code to flag emoji
        $flagOffset = 0x1F1E6;
        $asciiOffset = ord('A');

        $firstChar = mb_chr($flagOffset + ord($code[0]) - $asciiOffset);
        $secondChar = mb_chr($flagOffset + ord($code[1]) - $asciiOffset);

        return $firstChar . $secondChar;
    }

    /**
     * Generate relevant hashtags for the job.
     *
     * @return string
     */
    protected function generateHashtags()
    {
        $tags = ['#hiring', '#jobs', '#career'];

        // Country-specific hashtag
        $countryTag = '#' . strtolower(str_replace(' ', '', $this->getCountryName())) . 'jobs';
        $tags[] = $countryTag;

        // Industry/category tags based on title keywords
        $titleLower = strtolower($this->title);

        $industryTags = [
            'developer' => '#developer', 'engineer' => '#engineering',
            'software' => '#tech', 'marketing' => '#marketing',
            'sales' => '#sales', 'finance' => '#finance',
            'accounting' => '#accounting', 'admin' => '#administrative',
            'manager' => '#management', 'designer' => '#design',
            'nurse' => '#healthcare', 'teacher' => '#education',
            'driver' => '#logistics', 'chef' => '#hospitality',
            'security' => '#security', 'it ' => '#IT',
            'data' => '#data', 'analyst' => '#analytics',
        ];

        foreach ($industryTags as $keyword => $tag) {
            if (str_contains($titleLower, $keyword)) {
                $tags[] = $tag;
                break; // Only add one industry tag
            }
        }

        // Add remote work tag if applicable
        if (str_contains($titleLower, 'remote') || str_contains(strtolower($this->description ?? ''), 'remote')) {
            $tags[] = '#remotework';
        }

        return implode(' ', array_unique($tags));
    }

    /**
     * Format for WhatsApp/Telegram with cleaner markdown.
     *
     * @param  string  $shortUrl
     * @return string
     */
    public function formatForMessaging($shortUrl)
    {
        $location = $this->getCountryName();
        $flag = $this->getCountryFlag();
        $company = $this->company_name ? "\n🏢 *{$this->company_name}*" : "";
        $salary = $this->formatSalary();
        $salaryLine = $salary ? "\n💰 {$salary}" : "";

        return "🔥 *{$this->title}*{$company}

📍 {$flag} {$location}{$salaryLine}

{$this->getShortDescription(150)}

👉 *Apply:* {$shortUrl}

{$this->generateHashtags()}";
    }

    /**
     * Format for Twitter/X with character limit.
     *
     * @param  string  $shortUrl
     * @return string
     */
    public function formatForTwitter($shortUrl)
    {
        $location = $this->getCountryName();
        $flag = $this->getCountryFlag();
        $company = $this->company_name ? " @ {$this->company_name}" : "";
        $salary = $this->formatSalary();
        $salaryText = $salary ? " | {$salary}" : "";

        // Twitter has 280 char limit
        $base = "🔥 {$this->title}{$company}

{$flag} {$location}{$salaryText}

Apply: {$shortUrl}

#hiring #jobs";

        // Truncate if needed while keeping the link
        if (strlen($base) > 280) {
            $title = Str::limit($this->title, 60);
            $base = "🔥 {$title}

{$flag} {$location}

Apply: {$shortUrl}

#hiring #jobs";
        }

        return $base;
    }
}
