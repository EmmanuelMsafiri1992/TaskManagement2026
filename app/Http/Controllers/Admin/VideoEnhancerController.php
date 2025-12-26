<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoEnhancer;
use App\Jobs\ProcessVideoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VideoEnhancerController extends Controller
{
    /**
     * List user's videos
     */
    public function index()
    {
        $videos = VideoEnhancer::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'original_filename' => $video->original_filename,
                    'original_size' => $video->original_size,
                    'original_size_human' => $video->original_size_human,
                    'processed_size' => $video->processed_size,
                    'processed_size_human' => $video->processed_size_human,
                    'target_size' => $video->target_size,
                    'target_size_human' => $video->target_size_human,
                    'status' => $video->status,
                    'enhancement_options' => $video->enhancement_options,
                    'error_message' => $video->error_message,
                    'compression_ratio' => $video->compression_ratio,
                    'created_at' => $video->created_at->toISOString(),
                    'processing_started_at' => $video->processing_started_at?->toISOString(),
                    'processing_completed_at' => $video->processing_completed_at?->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    /**
     * Upload a new video
     */
    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/webm|max:2097152', // 2GB in KB
        ]);

        $file = $request->file('video');
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Store the file
        $path = $file->store('videos/uploads');

        $video = VideoEnhancer::create([
            'user_id' => Auth::id(),
            'original_filename' => $originalFilename,
            'original_path' => $path,
            'original_size' => $fileSize,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'data' => [
                'id' => $video->id,
                'original_filename' => $video->original_filename,
                'original_size' => $video->original_size,
                'original_size_human' => $video->original_size_human,
                'status' => $video->status,
            ],
        ]);
    }

    /**
     * Estimate output size based on options
     */
    public function estimate(Request $request, $id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->findOrFail($id);

        $options = $request->validate([
            'upscale' => 'boolean',
            'upscale_resolution' => 'nullable|string|in:1920:1080,2560:1440,3840:2160',
            'enhance_quality' => 'boolean',
            'noise_reduction' => 'boolean',
            'noise_level' => 'nullable|integer|min:-50|max:0',
            'normalize_audio' => 'boolean',
            'compression_level' => 'nullable|integer|min:18|max:35',
            'preset' => 'nullable|string|in:ultrafast,superfast,veryfast,faster,fast,medium,slow,slower,veryslow',
            'format' => 'nullable|string|in:mp4,webm,mkv',
        ]);

        $estimate = ProcessVideoJob::estimateOutputSize($video->original_size, $options);

        // Check feasibility based on target size
        $targetSize = $request->input('target_size');
        $feasible = true;
        $feasibilityMessage = 'Estimated output size looks achievable.';

        if ($targetSize) {
            $targetBytes = $targetSize * 1024 * 1024; // Convert MB to bytes
            if ($estimate['estimated_size'] > $targetBytes * 1.2) { // Allow 20% margin
                $feasible = false;
                $feasibilityMessage = 'Target size may not be achievable. Try increasing compression level or reducing resolution.';
            } elseif ($estimate['estimated_size'] < $targetBytes * 0.5) {
                $feasibilityMessage = 'Target size is generous. You could reduce compression for better quality.';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'original_size' => $video->original_size,
                'original_size_human' => $video->original_size_human,
                'estimated_size' => $estimate['estimated_size'],
                'estimated_size_human' => $estimate['estimated_size_human'],
                'compression_ratio' => $estimate['compression_ratio'],
                'feasible' => $feasible,
                'feasibility_message' => $feasibilityMessage,
            ],
        ]);
    }

    /**
     * Start processing the video
     */
    public function process(Request $request, $id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $options = $request->validate([
            'upscale' => 'boolean',
            'upscale_resolution' => 'nullable|string|in:1920:1080,2560:1440,3840:2160',
            'enhance_quality' => 'boolean',
            'noise_reduction' => 'boolean',
            'noise_level' => 'nullable|integer|min:-50|max:0',
            'normalize_audio' => 'boolean',
            'compression_level' => 'nullable|integer|min:18|max:35',
            'preset' => 'nullable|string|in:ultrafast,superfast,veryfast,faster,fast,medium,slow,slower,veryslow',
            'format' => 'nullable|string|in:mp4,webm,mkv',
            'target_size' => 'nullable|integer', // in MB
        ]);

        $video->update([
            'enhancement_options' => $options,
            'target_size' => isset($options['target_size']) ? $options['target_size'] * 1024 * 1024 : null,
        ]);

        // Dispatch the job
        ProcessVideoJob::dispatch($video);

        return response()->json([
            'success' => true,
            'message' => 'Video processing started',
            'data' => [
                'id' => $video->id,
                'status' => 'processing',
            ],
        ]);
    }

    /**
     * Get video status
     */
    public function status($id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $video->id,
                'status' => $video->status,
                'processed_size' => $video->processed_size,
                'processed_size_human' => $video->processed_size_human,
                'compression_ratio' => $video->compression_ratio,
                'error_message' => $video->error_message,
                'processing_started_at' => $video->processing_started_at?->toISOString(),
                'processing_completed_at' => $video->processing_completed_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Download processed video
     */
    public function download($id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->findOrFail($id);

        if (!$video->processed_path) {
            return response()->json([
                'success' => false,
                'message' => 'Processed video not found',
            ], 404);
        }

        $path = storage_path('app/' . $video->processed_path);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Processed file not found on disk',
            ], 404);
        }

        $filename = pathinfo($video->original_filename, PATHINFO_FILENAME) . '_enhanced.' .
                    ($video->enhancement_options['format'] ?? 'mp4');

        return response()->download($path, $filename);
    }

    /**
     * Delete video and its files
     */
    public function destroy($id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->findOrFail($id);

        // Delete original file
        if ($video->original_path && Storage::exists($video->original_path)) {
            Storage::delete($video->original_path);
        }

        // Delete processed file
        if ($video->processed_path && Storage::exists($video->processed_path)) {
            Storage::delete($video->processed_path);
        }

        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully',
        ]);
    }

    /**
     * Delete only the processed video (keep original)
     */
    public function deleteProcessed($id)
    {
        $video = VideoEnhancer::where('user_id', Auth::id())
            ->findOrFail($id);

        // Delete only processed file
        if ($video->processed_path && Storage::exists($video->processed_path)) {
            Storage::delete($video->processed_path);
        }

        $video->update([
            'processed_path' => null,
            'processed_size' => null,
            'status' => 'pending',
            'processing_started_at' => null,
            'processing_completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Processed video deleted. You can re-process with different settings.',
        ]);
    }
}
