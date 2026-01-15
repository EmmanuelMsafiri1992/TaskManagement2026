<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordingSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_provider_id',
        'subject_id',
        'topic_id',
        'lesson_plan_id',
        'clock_in',
        'clock_out',
        'recording_start',
        'recording_end',
        'total_minutes',
        'recording_minutes',
        'video_file',
        'video_duration',
        'retakes',
        'status',
        'quality_rating',
        'notes',
        'admin_notes',
        'approved_by',
        'approved_at',
        'meta',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'recording_start' => 'datetime',
        'recording_end' => 'datetime',
        'approved_at' => 'datetime',
        'meta' => 'array',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function lessonPlan()
    {
        return $this->belongsTo(LessonPlan::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function resources()
    {
        return $this->hasMany(SessionResource::class);
    }

    public function clockIn()
    {
        $this->update(['clock_in' => now(), 'status' => 'in_progress']);
    }

    public function clockOut()
    {
        $clockOut = now();
        $totalMinutes = $this->clock_in ? $this->clock_in->diffInMinutes($clockOut) : 0;

        $this->update([
            'clock_out' => $clockOut,
            'total_minutes' => $totalMinutes,
            'status' => 'completed',
        ]);
    }

    public function startRecording()
    {
        $this->update(['recording_start' => now()]);
    }

    public function stopRecording()
    {
        $recordingEnd = now();
        $recordingMinutes = $this->recording_start ? $this->recording_start->diffInMinutes($recordingEnd) : 0;

        $this->update([
            'recording_end' => $recordingEnd,
            'recording_minutes' => $recordingMinutes,
        ]);
    }

    public function approve(User $user)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $user, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
