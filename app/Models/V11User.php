<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class V11User extends Model
{
    use HasFactory;

    protected $connection = 'v11';
    protected $table = 'users';

    /**
     * Scope to get only verified email users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope to get only job seekers
     */
    public function scopeJobSeekers($query)
    {
        return $query->where('user_type_id', 2);
    }

    /**
     * Scope to get only employers
     */
    public function scopeEmployers($query)
    {
        return $query->where('user_type_id', 1);
    }

    /**
     * Check if user is already assigned in TaskHub
     */
    public function isAssigned()
    {
        return UserAssignment::where('v11_user_id', $this->id)
            ->where('v11_user_type', $this->user_type_id)
            ->exists();
    }

    /**
     * Get the assignment for this user
     */
    public function getAssignment()
    {
        return UserAssignment::where('v11_user_id', $this->id)
            ->where('v11_user_type', $this->user_type_id)
            ->first();
    }

    /**
     * Get user type name
     */
    public function getUserTypeName()
    {
        return $this->user_type_id == 1 ? 'Employer' : 'Job Seeker';
    }
}
