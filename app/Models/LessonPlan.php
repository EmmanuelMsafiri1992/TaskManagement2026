<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'service_provider_id',
        'title',
        'objectives',
        'introduction',
        'main_content',
        'activities',
        'assessment',
        'conclusion',
        'homework',
        'duration_minutes',
        'status',
        'approved_by',
        'approved_at',
        'feedback',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recordingSessions()
    {
        return $this->hasMany(RecordingSession::class);
    }

    public function submit()
    {
        $this->update(['status' => 'submitted']);
    }

    public function approve(User $user)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $user, $feedback = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'feedback' => $feedback,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
