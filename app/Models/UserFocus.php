<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFocus extends Model
{
    use HasFactory;

    protected $table = 'user_focus';

    protected $fillable = [
        'user_id',
        'works_with_job_seekers',
        'works_with_employers',
        'enable_auto_assign_job_seekers',
        'enable_auto_assign_employers',
    ];

    protected $casts = [
        'works_with_job_seekers' => 'boolean',
        'works_with_employers' => 'boolean',
        'enable_auto_assign_job_seekers' => 'boolean',
        'enable_auto_assign_employers' => 'boolean',
    ];

    /**
     * Get the user that owns this focus
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
