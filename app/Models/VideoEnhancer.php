<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoEnhancer extends Model
{
    protected $table = 'video_enhancers';

    protected $fillable = [
        'user_id',
        'original_filename',
        'original_path',
        'original_size',
        'processed_path',
        'processed_size',
        'target_size',
        'status',
        'enhancement_options',
        'error_message',
        'processing_started_at',
        'processing_completed_at',
    ];

    protected $casts = [
        'original_size' => 'integer',
        'processed_size' => 'integer',
        'target_size' => 'integer',
        'enhancement_options' => 'array',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human readable file size
     */
    public function getOriginalSizeHumanAttribute(): string
    {
        return $this->formatBytes($this->original_size);
    }

    public function getProcessedSizeHumanAttribute(): string
    {
        return $this->processed_size ? $this->formatBytes($this->processed_size) : '-';
    }

    public function getTargetSizeHumanAttribute(): string
    {
        return $this->target_size ? $this->formatBytes($this->target_size) : '-';
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Check if processing is complete
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if processing failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if currently processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Get compression ratio
     */
    public function getCompressionRatioAttribute(): ?float
    {
        if (!$this->processed_size || !$this->original_size) {
            return null;
        }

        return round((1 - ($this->processed_size / $this->original_size)) * 100, 1);
    }
}
