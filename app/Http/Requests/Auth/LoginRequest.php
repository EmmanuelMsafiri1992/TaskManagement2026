<?php

namespace App\Http\Requests\Auth;

use App\Models\ServiceProvider;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * The authenticated guard (set after successful authentication).
     *
     * @var string|null
     */
    protected $authenticatedGuard = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the guard that was used for authentication.
     *
     * @return string|null
     */
    public function getAuthenticatedGuard()
    {
        return $this->authenticatedGuard;
    }

    /**
     * Check if the authenticated user is a teacher/service provider.
     *
     * @return bool
     */
    public function isTeacherLogin()
    {
        return $this->authenticatedGuard === 'service_provider';
    }

    /**
     * Attempt to authenticate the request's credentials.
     * First tries the regular user guard, then the service provider guard.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        // First, try to authenticate as a regular user (employee)
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $this->authenticatedGuard = 'web';
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // If that fails, try to authenticate as a service provider (teacher)
        if (Auth::guard('service_provider')->attempt($credentials, $remember)) {
            $provider = Auth::guard('service_provider')->user();

            // Check service provider status
            if ($provider->status === 'suspended') {
                Auth::guard('service_provider')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Your account has been suspended. Please contact support.',
                ]);
            }

            if ($provider->status === 'terminated') {
                Auth::guard('service_provider')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Your account has been terminated.',
                ]);
            }

            $this->authenticatedGuard = 'service_provider';
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Both attempts failed
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
