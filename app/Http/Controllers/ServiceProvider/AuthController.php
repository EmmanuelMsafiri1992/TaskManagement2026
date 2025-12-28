<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('service_provider')->check()) {
            return redirect()->route('service-provider.dashboard');
        }

        return view('service-provider.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('service_provider')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $provider = Auth::guard('service_provider')->user();

            if ($provider->status === 'suspended') {
                Auth::guard('service_provider')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been suspended. Please contact support.',
                ]);
            }

            if ($provider->status === 'terminated') {
                Auth::guard('service_provider')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been terminated.',
                ]);
            }

            // Check if agreement is signed
            if (!$provider->agreement_signed) {
                return redirect()->route('service-provider.agreement');
            }

            return redirect()->intended(route('service-provider.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegister()
    {
        if (Auth::guard('service_provider')->check()) {
            return redirect()->route('service-provider.dashboard');
        }

        return view('service-provider.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:service_providers,email',
            'phone' => 'required|string|max:20',
            'national_id' => 'required|string|max:50|unique:service_providers,national_id',
            'address' => 'nullable|string|max:500',
            'specialty' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            // Payment fields
            'payment_preference' => 'nullable|in:monthly,lump_sum',
            'monthly_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank,mobile_money',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'mobile_money_provider' => 'nullable|string|max:255',
            'mobile_money_number' => 'nullable|string|max:255',
            'mobile_money_name' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'pending';
        $validated['total_agreed_amount'] = 700000.00; // Default contract amount

        $provider = ServiceProvider::create($validated);

        // Log them in and redirect to agreement page
        Auth::guard('service_provider')->login($provider);

        return redirect()->route('service-provider.agreement');
    }

    public function logout(Request $request)
    {
        Auth::guard('service_provider')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showAgreement()
    {
        $provider = Auth::guard('service_provider')->user();

        if ($provider->agreement_signed) {
            return redirect()->route('service-provider.dashboard');
        }

        $templatePath = storage_path('app/agreement_template.html');
        $agreementContent = file_exists($templatePath)
            ? file_get_contents($templatePath)
            : $this->getDefaultAgreement();

        // Replace placeholders
        $agreementContent = str_replace(
            ['{{provider_name}}', '{{current_date}}'],
            [$provider->name, now()->format('F j, Y')],
            $agreementContent
        );

        return view('service-provider.auth.agreement', [
            'provider' => $provider,
            'agreementContent' => $agreementContent,
        ]);
    }

    public function signAgreement(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
            'agree' => 'required|accepted',
        ]);

        $provider = Auth::guard('service_provider')->user();

        $templatePath = storage_path('app/agreement_template.html');
        $agreementContent = file_exists($templatePath)
            ? file_get_contents($templatePath)
            : $this->getDefaultAgreement();

        // Create agreement record
        ServiceProviderAgreement::create([
            'service_provider_id' => $provider->id,
            'agreement_content' => $agreementContent,
            'signature' => $request->signature,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signed_at' => now(),
        ]);

        // Update provider
        $provider->update(['agreement_signed' => true]);

        return redirect()->route('service-provider.dashboard')
            ->with('success', 'Agreement signed successfully. Welcome!');
    }

    protected function getDefaultAgreement()
    {
        return <<<HTML
<h1>SERVICE PROVIDER AGREEMENT</h1>

<p>This Agreement is entered into between:</p>

<p><strong>The Company:</strong> Studyseco.com<br>
<strong>Address:</strong> Blantyre, Malawi<br>
<strong>Email:</strong> info@studyseco.com<br>
<strong>The Service Provider:</strong> {{provider_name}}</p>

<h2>1. SERVICES</h2>
<p>The Service Provider agrees to provide educational content recording services including but not limited to:</p>
<ul>
    <li>Recording video lessons according to the Malawian Secondary School Syllabus</li>
    <li>Preparing lesson plans as required</li>
    <li>Following quality standards set by the Company</li>
</ul>

<h2>2. INTELLECTUAL PROPERTY</h2>
<p>The Service Provider hereby acknowledges and agrees that:</p>
<ul>
    <li>All content created during the engagement belongs exclusively to the Company</li>
    <li>The Service Provider retains no rights to the recorded materials</li>
    <li>The Company may use, modify, distribute, and commercialize all content without restriction</li>
    <li>The Service Provider waives any moral rights to the content</li>
</ul>

<h2>3. COMPENSATION</h2>
<p>The Service Provider will be compensated at the agreed hourly rate for:</p>
<ul>
    <li>Time spent recording lessons</li>
    <li>Time spent preparing lesson plans (if applicable)</li>
</ul>

<h2>4. CONFIDENTIALITY</h2>
<p>The Service Provider agrees to maintain confidentiality of all proprietary information, methods, and materials.</p>

<h2>5. TERMINATION</h2>
<p>Either party may terminate this agreement with written notice. Upon termination, all intellectual property rights remain with the Company.</p>

<h2>6. ACKNOWLEDGMENT</h2>
<p>By signing below, the Service Provider acknowledges that they have read, understood, and agree to all terms of this Agreement.</p>

<p><strong>Date:</strong> {{current_date}}</p>
HTML;
    }
}
