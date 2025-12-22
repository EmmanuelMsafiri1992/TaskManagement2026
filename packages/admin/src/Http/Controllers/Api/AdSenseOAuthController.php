<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdSenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdSenseOAuthController extends Controller
{
    protected $adSenseService;

    public function __construct(AdSenseService $adSenseService)
    {
        $this->middleware('can:setting:adsense');
        $this->adSenseService = $adSenseService;
    }

    /**
     * Get OAuth authorization URL
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUrl()
    {
        try {
            $authUrl = $this->adSenseService->getAuthUrl();

            return response()->json([
                'auth_url' => $authUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get auth URL', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to generate authorization URL: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle OAuth callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            Log::error('OAuth callback error', ['error' => $error]);
            return redirect('/admin/settings/adsense')->with('error', 'Authorization failed: ' . $error);
        }

        if (!$code) {
            return redirect('/admin/settings/adsense')->with('error', 'No authorization code received');
        }

        try {
            $success = $this->adSenseService->handleCallback($code);

            if ($success) {
                return redirect('/admin/settings/adsense')->with('success', 'AdSense connected successfully!');
            } else {
                return redirect('/admin/settings/adsense')->with('error', 'Failed to connect AdSense. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('OAuth callback exception', ['error' => $e->getMessage()]);
            return redirect('/admin/settings/adsense')->with('error', 'Connection error: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect AdSense OAuth
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnect()
    {
        try {
            $success = $this->adSenseService->disconnect();

            if ($success) {
                return response()->json([
                    'message' => 'AdSense disconnected successfully',
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to disconnect AdSense',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to disconnect AdSense', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Disconnect error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
