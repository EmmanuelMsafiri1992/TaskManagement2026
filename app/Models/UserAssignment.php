<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'taskhub_user_id',
        'task_id',
        'v11_user_id',
        'v11_user_type',
        'v11_user_name',
        'v11_user_email',
        'auto_assigned',
        'assigned_by',
        'assigned_at',
        'last_contacted_at',
        'notes',
    ];

    protected $casts = [
        'auto_assigned' => 'boolean',
        'assigned_at' => 'datetime',
        'last_contacted_at' => 'datetime',
    ];

    /**
     * Get the task associated with this assignment
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the TaskHub user this assignment belongs to
     */
    public function taskhubUser()
    {
        return $this->belongsTo(User::class, 'taskhub_user_id');
    }

    /**
     * Get the user who made this assignment
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the V11 user (from secondary database)
     */
    public function v11User()
    {
        return V11User::on('v11')->find($this->v11_user_id);
    }

    /**
     * Check if this is a job seeker assignment
     */
    public function isJobSeeker()
    {
        return $this->v11_user_type == 2;
    }

    /**
     * Check if this is an employer assignment
     */
    public function isEmployer()
    {
        return $this->v11_user_type == 1;
    }

    /**
     * Get user type name
     */
    public function getUserTypeName()
    {
        return $this->v11_user_type == 1 ? 'Employer' : 'Job Seeker';
    }
}
