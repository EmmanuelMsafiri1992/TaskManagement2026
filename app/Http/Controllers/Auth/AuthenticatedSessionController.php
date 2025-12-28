<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Handle teacher login redirect
        if ($request->isTeacherLogin()) {
            $provider = Auth::guard('service_provider')->user();

            // Check if agreement is signed
            if (!$provider->agreement_signed) {
                return redirect()->route('service-provider.agreement');
            }

            return redirect()->route('service-provider.dashboard');
        }

        // Handle employee login redirect
        // Clear any invalid intended URL
        $intended = session()->pull('url.intended', RouteServiceProvider::HOME);

        // If the intended URL contains .well-known or other invalid paths, redirect to home
        if (str_contains($intended, '.well-known') || str_contains($intended, 'appspecific')) {
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect($intended);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        // Logout from both guards
        Auth::guard('web')->logout();
        Auth::guard('service_provider')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
