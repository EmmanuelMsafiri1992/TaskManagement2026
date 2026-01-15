<?php

namespace Admin\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController
{
    /**
     * Impersonate a user
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function impersonate(Request $request, $userId)
    {
        $currentUser = Auth::user();

        // Only super admins can impersonate
        if (!$currentUser->isSuperAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only super admins can impersonate users.'
            ], 403);
        }

        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Cannot impersonate yourself
        if ($currentUser->id === $targetUser->id) {
            return response()->json([
                'message' => 'You cannot impersonate yourself.'
            ], 400);
        }

        // Store original user ID in session
        Session::put('impersonator_id', $currentUser->id);
        Session::put('impersonating', true);

        // Login as the target user (remember = true to persist the session)
        Auth::login($targetUser, true);

        // Save the session explicitly
        Session::save();

        return response()->json([
            'message' => 'Now impersonating ' . $targetUser->name,
            'user' => $targetUser->only(['id', 'name', 'email', 'avatar']),
            'impersonating' => true,
            'redirect' => '/'
        ]);
    }

    /**
     * Stop impersonating and return to original user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopImpersonating(Request $request)
    {
        if (!Session::has('impersonator_id')) {
            return response()->json([
                'message' => 'You are not impersonating anyone.'
            ], 400);
        }

        $originalUserId = Session::get('impersonator_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            return response()->json([
                'message' => 'Original user not found.'
            ], 404);
        }

        // Clear impersonation session data
        Session::forget('impersonator_id');
        Session::forget('impersonating');

        // Login back as original user (remember = true to persist)
        Auth::login($originalUser, true);

        // Save the session explicitly
        Session::save();

        return response()->json([
            'message' => 'Stopped impersonating. Welcome back, ' . $originalUser->name,
            'user' => $originalUser->only(['id', 'name', 'email', 'avatar']),
            'impersonating' => false,
            'redirect' => '/'
        ]);
    }

    /**
     * Check if currently impersonating
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $isImpersonating = Session::has('impersonator_id');
        $impersonatorId = Session::get('impersonator_id');

        return response()->json([
            'impersonating' => $isImpersonating,
            'impersonator_id' => $impersonatorId
        ]);
    }
}
