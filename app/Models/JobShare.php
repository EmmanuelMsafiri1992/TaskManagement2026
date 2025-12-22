<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * JobShare Model - Tracks job postings shared by users
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $task_id
 * @property int $v11_post_id
 * @property string $post_title
 * @property string $post_url
 * @property string $country_code
 * @property string|null $shortened_url
 * @property string|null $formatted_content
 * @property bool $copied
 * @property \Illuminate\Support\Carbon|null $copied_at
 * @property \Illuminate\Support\Carbon $assigned_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class JobShare extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'task_id',
        'v11_post_id',
        'post_title',
        'post_url',
        'country_code',
        'shortened_url',
        'formatted_content',
        'copied',
        'copied_at',
        'assigned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'copied' => 'boolean',
        'copied_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user who was assigned this job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task associated with this job share.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Mark the job as copied.
     *
     * @return bool
     */
    public function markAsCopied()
    {
        return $this->update([
            'copied' => true,
            'copied_at' => now(),
        ]);
    }

    /**
     * Scope to get uncopied jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUncopied($query)
    {
        return $query->where('copied', false);
    }

    /**
     * Scope to get copied jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCopied($query)
    {
        return $query->where('copied', true);
    }

    /**
     * Scope to filter by user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by country.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $countryCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
}
