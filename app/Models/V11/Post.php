<?php

namespace App\Models\V11;

use Illuminate\Database\Eloquent\Model;

/**
 * V11 Post Model - Represents job postings from the secondary database
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $country_code
 * @property string $application_url
 * @property string $company_name
 * @property string $company_description
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
    ];

    /**
     * Scope to get recent posts (last 8 hours).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(8))
                     ->whereNotNull('email_verified_at')
                     ->whereNull('deleted_at')
                     ->whereNull('archived_at')
                     ->whereNotNull('application_url');
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
     * Get formatted description (shortened for social media).
     *
     * @param  int  $length
     * @return string
     */
    public function getShortDescription($length = 150)
    {
        $description = strip_tags($this->description);

        if (strlen($description) <= $length) {
            return $description;
        }

        return substr($description, 0, $length) . '...';
    }

    /**
     * Get the application URL for this post.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->application_url;
    }

    /**
     * Format job for social media sharing.
     *
     * @param  string  $shortUrl
     * @return string
     */
    public function formatForSharing($shortUrl)
    {
        $companyInfo = $this->company_name ? " at {$this->company_name}" : "";

        $formattedText = "🔥 {$this->title}{$companyInfo}\n\n";
        $formattedText .= $this->getShortDescription() . "\n\n";

        // Add salary info if available
        if ($this->salary_min || $this->salary_max) {
            $salaryText = $this->formatSalary();
            if ($salaryText) {
                $formattedText .= "💰 {$salaryText}\n\n";
            }
        }

        $formattedText .= "Apply now: {$shortUrl}\n\n";
        $formattedText .= "#hiring #jobs #career #" . strtolower($this->country_code) . "jobs";

        return $formattedText;
    }

    /**
     * Format salary information.
     *
     * @return string|null
     */
    protected function formatSalary()
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        if ($this->salary_min && $this->salary_max) {
            return "Salary: $" . number_format($this->salary_min) . " - $" . number_format($this->salary_max);
        }

        if ($this->salary_min) {
            return "Salary: From $" . number_format($this->salary_min);
        }

        return "Salary: Up to $" . number_format($this->salary_max);
    }
}
