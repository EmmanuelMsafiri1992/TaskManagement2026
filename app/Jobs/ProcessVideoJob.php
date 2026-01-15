<?php

namespace App\Jobs;

use App\Models\VideoEnhancer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout for large videos
    public $tries = 1; // Only try once since video processing is expensive

    protected VideoEnhancer $videoEnhancer;

    public function __construct(VideoEnhancer $videoEnhancer)
    {
        $this->videoEnhancer = $videoEnhancer;
    }

    public function handle(): void
    {
        try {
            $this->videoEnhancer->update([
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            $inputPath = storage_path('app/' . $this->videoEnhancer->original_path);
            $options = $this->videoEnhancer->enhancement_options ?? [];

            // Generate output filename
            $outputFilename = pathinfo($this->videoEnhancer->original_filename, PATHINFO_FILENAME);
            $outputFormat = $options['format'] ?? 'mp4';
            $outputFilename .= '_enhanced.' . $outputFormat;
            $outputPath = storage_path('app/videos/processed/' . $this->videoEnhancer->id . '_' . $outputFilename);

            // Ensure output directory exists
            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            // Build FFmpeg command
            $command = $this->buildFFmpegCommand($inputPath, $outputPath, $options);

            // Execute FFmpeg
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('FFmpeg processing failed: ' . implode("\n", $output));
            }

            // Check if output file was created
            if (!file_exists($outputPath)) {
                throw new \Exception('Output file was not created');
            }

            $processedSize = filesize($outputPath);
            $relativePath = 'videos/processed/' . $this->videoEnhancer->id . '_' . $outputFilename;

            $this->videoEnhancer->update([
                'status' => 'completed',
                'processed_path' => $relativePath,
                'processed_size' => $processedSize,
                'processing_completed_at' => now(),
            ]);

            Log::info('Video processing completed', [
                'id' => $this->videoEnhancer->id,
                'original_size' => $this->videoEnhancer->original_size,
                'processed_size' => $processedSize,
            ]);

        } catch (\Exception $e) {
            Log::error('Video processing failed', [
                'id' => $this->videoEnhancer->id,
                'error' => $e->getMessage(),
            ]);

            $this->videoEnhancer->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_completed_at' => now(),
            ]);
        }
    }

    private function buildFFmpegCommand(string $inputPath, string $outputPath, array $options): string
    {
        $ffmpegPath = $this->getFFmpegPath();
        $videoFilters = [];
        $audioFilters = [];

        // Upscaling
        if (!empty($options['upscale'])) {
            $resolution = $options['upscale_resolution'] ?? '1920:1080';
            $videoFilters[] = "scale={$resolution}:flags=lanczos";
        }

        // Video stabilization / quality enhancement
        if (!empty($options['enhance_quality'])) {
            $videoFilters[] = "unsharp=5:5:0.8:3:3:0.4";
        }

        // Audio noise reduction
        if (!empty($options['noise_reduction'])) {
            $noiseLevel = $options['noise_level'] ?? -25;
            $audioFilters[] = "afftdn=nf={$noiseLevel}";
        }

        // Audio normalization
        if (!empty($options['normalize_audio'])) {
            $audioFilters[] = "loudnorm=I=-16:TP=-1.5:LRA=11";
        }

        // Build command
        $cmd = "\"{$ffmpegPath}\" -i \"{$inputPath}\" -y";

        // Video filters
        if (!empty($videoFilters)) {
            $cmd .= ' -vf "' . implode(',', $videoFilters) . '"';
        }

        // Audio filters
        if (!empty($audioFilters)) {
            $cmd .= ' -af "' . implode(',', $audioFilters) . '"';
        }

        // Compression settings
        $crf = $options['compression_level'] ?? 23;
        $preset = $options['preset'] ?? 'medium';
        $cmd .= " -c:v libx264 -crf {$crf} -preset {$preset}";

        // Audio codec
        $cmd .= " -c:a aac -b:a 128k";

        // Output format specific options
        $format = $options['format'] ?? 'mp4';
        if ($format === 'mp4') {
            $cmd .= " -movflags +faststart";
        }

        $cmd .= " \"{$outputPath}\"";

        return $cmd;
    }

    private function getFFmpegPath(): string
    {
        // Check common FFmpeg locations
        $paths = [
            'ffmpeg', // In PATH
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'C:\\laragon\\bin\\ffmpeg\\ffmpeg.exe',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
        ];

        foreach ($paths as $path) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                $output = [];
                exec("where {$path} 2>nul", $output);
                if (!empty($output)) {
                    return $path;
                }
            } else {
                $output = [];
                exec("which {$path} 2>/dev/null", $output);
                if (!empty($output)) {
                    return $path;
                }
            }
        }

        // Default to 'ffmpeg' and hope it's in PATH
        return 'ffmpeg';
    }

    /**
     * Estimate output size based on options
     */
    public static function estimateOutputSize(int $originalSize, array $options): array
    {
        $crf = $options['compression_level'] ?? 23;

        // CRF compression estimation (rough approximation)
        // CRF 18 = ~same size, CRF 23 = ~50%, CRF 28 = ~30%, CRF 35 = ~15%
        $compressionRatios = [
            18 => 1.0,
            19 => 0.9,
            20 => 0.8,
            21 => 0.7,
            22 => 0.6,
            23 => 0.5,
            24 => 0.45,
            25 => 0.4,
            26 => 0.35,
            27 => 0.32,
            28 => 0.3,
            29 => 0.27,
            30 => 0.25,
            35 => 0.15,
        ];

        $ratio = $compressionRatios[$crf] ?? 0.5;

        // Upscaling increases size
        if (!empty($options['upscale'])) {
            $resolution = $options['upscale_resolution'] ?? '1920:1080';
            if ($resolution === '3840:2160') { // 4K
                $ratio *= 2.5;
            } elseif ($resolution === '2560:1440') { // 2K
                $ratio *= 1.5;
            }
        }

        $estimatedSize = (int) ($originalSize * $ratio);

        return [
            'estimated_size' => $estimatedSize,
            'estimated_size_human' => self::formatBytes($estimatedSize),
            'compression_ratio' => round((1 - $ratio) * 100, 1),
            'ratio' => $ratio,
        ];
    }

    private static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
