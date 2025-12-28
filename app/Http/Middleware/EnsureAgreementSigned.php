<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAgreementSigned
{
    public function handle(Request $request, Closure $next)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($provider && !$provider->agreement_signed) {
            return redirect()->route('service-provider.agreement');
        }

        return $next($request);
    }
}
