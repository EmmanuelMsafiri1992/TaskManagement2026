<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class PublicLeadController extends Controller
{
    public function store(Request $request)
    {
        // Rate limiting: max 5 submissions per IP per hour
        $key = 'lead-submission:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many submissions. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 3600);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:191',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'service_interest' => 'required|in:web_development,mobile_app,software_development,ui_ux_design,ecommerce,maintenance,consulting,other',
            'project_description' => 'required|string|min:20|max:5000',
            'budget_range' => 'nullable|in:under_500,500_1000,1000_5000,5000_10000,10000_plus,not_sure',
            'timeline' => 'nullable|in:immediate,1_month,1_3_months,3_6_months,flexible',
            // UTM parameters (optional)
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:100',
            'utm_term' => 'nullable|string|max:100',
            'landing_page' => 'nullable|string|max:500',
            'referrer_url' => 'nullable|string|max:500',
            // Honeypot field for spam protection
            'website_url' => 'nullable|max:0', // Should be empty (honeypot)
        ]);

        // Honeypot check
        if (!empty($validated['website_url'])) {
            // Bot detected, return success but don't save
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your submission!',
            ]);
        }

        // Remove honeypot from data
        unset($validated['website_url']);

        // Add tracking data
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['source'] = $this->determineSource($request);
        $validated['status'] = 'new';
        $validated['priority'] = $this->determinePriority($validated);

        // Check for existing lead with same email in last 24 hours
        $existingLead = Lead::where('email', $validated['email'])
                           ->where('created_at', '>=', now()->subDay())
                           ->first();

        if ($existingLead) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! We already have your inquiry and will contact you soon.',
            ]);
        }

        $lead = Lead::create($validated);
        $lead->updateScore();

        // Log activity
        $lead->logActivity('form_submission', 'Form Submitted', 'Lead submitted via public form');

        // Notify admins
        $this->notifyAdmins($lead);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your inquiry! We will contact you within 24 hours.',
            'lead_id' => $lead->id,
        ], 201);
    }

    public function options()
    {
        return response()->json([
            'service_interests' => Lead::SERVICE_INTERESTS,
            'budget_ranges' => Lead::BUDGET_RANGES,
            'timelines' => Lead::TIMELINES,
        ]);
    }

    private function determineSource(Request $request): string
    {
        $utmSource = $request->input('utm_source');

        if ($utmSource) {
            return match (strtolower($utmSource)) {
                'google' => 'google_ads',
                'facebook', 'fb' => 'facebook',
                'linkedin' => 'linkedin',
                'twitter', 'x' => 'twitter',
                'email' => 'email_campaign',
                default => 'website',
            };
        }

        $referrer = $request->input('referrer_url', $request->header('Referer'));

        if ($referrer) {
            if (str_contains($referrer, 'google.com')) return 'google_ads';
            if (str_contains($referrer, 'facebook.com')) return 'facebook';
            if (str_contains($referrer, 'linkedin.com')) return 'linkedin';
            if (str_contains($referrer, 'twitter.com') || str_contains($referrer, 'x.com')) return 'twitter';
        }

        return 'website';
    }

    private function determinePriority(array $data): string
    {
        // Hot: Immediate timeline + high budget
        if (
            ($data['timeline'] ?? '') === 'immediate' &&
            in_array($data['budget_range'] ?? '', ['5000_10000', '10000_plus'])
        ) {
            return 'hot';
        }

        // Warm: Has budget or urgent timeline
        if (
            in_array($data['budget_range'] ?? '', ['1000_5000', '5000_10000', '10000_plus']) ||
            in_array($data['timeline'] ?? '', ['immediate', '1_month'])
        ) {
            return 'warm';
        }

        return 'cold';
    }

    private function notifyAdmins(Lead $lead)
    {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->get();

            if ($admins->isEmpty()) {
                $admins = User::take(1)->get();
            }

            Notification::send($admins, new NewLeadNotification($lead));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send lead notification: ' . $e->getMessage());
        }
    }
}
