<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'recording_session_id',
        'name',
        'type',
        'file_path',
        'file_size',
        'mime_type',
        'description',
    ];

    public function recordingSession()
    {
        return $this->belongsTo(RecordingSession::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? '/' . $this->file_path : null;
    }
}
