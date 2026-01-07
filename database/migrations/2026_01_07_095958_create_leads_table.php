<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Contact Information
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('website')->nullable();

            // Location
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // Lead Details
            $table->enum('service_interest', [
                'web_development',
                'mobile_app',
                'software_development',
                'ui_ux_design',
                'ecommerce',
                'maintenance',
                'consulting',
                'other'
            ])->default('web_development');
            $table->text('project_description')->nullable();
            $table->enum('budget_range', [
                'under_500',
                '500_1000',
                '1000_5000',
                '5000_10000',
                '10000_plus',
                'not_sure'
            ])->nullable();
            $table->enum('timeline', [
                'immediate',
                '1_month',
                '1_3_months',
                '3_6_months',
                'flexible'
            ])->nullable();

            // Lead Qualification
            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'proposal_sent',
                'negotiation',
                'won',
                'lost'
            ])->default('new');
            $table->enum('priority', ['hot', 'warm', 'cold'])->default('warm');
            $table->integer('score')->default(0); // Lead scoring 0-100

            // Source Tracking
            $table->enum('source', [
                'website',
                'referral',
                'social_media',
                'google_ads',
                'linkedin',
                'facebook',
                'twitter',
                'email_campaign',
                'cold_outreach',
                'other'
            ])->default('website');
            $table->string('source_detail')->nullable(); // e.g., specific campaign name

            // UTM Parameters for tracking
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('landing_page')->nullable();
            $table->string('referrer_url')->nullable();

            // Technical Data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Follow-up
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->text('internal_notes')->nullable();

            // Conversion
            $table->foreignId('converted_to_client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->string('loss_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });

        // Lead Activities/Interactions tracking
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', [
                'created',
                'email_sent',
                'email_opened',
                'email_clicked',
                'call_made',
                'call_received',
                'meeting_scheduled',
                'meeting_completed',
                'proposal_sent',
                'proposal_viewed',
                'status_changed',
                'note_added',
                'follow_up_scheduled',
                'website_visit',
                'form_submission'
            ]);
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'created_at']);
        });

        // Email templates for lead follow-ups
        Schema::create('lead_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->enum('type', [
                'initial_contact',
                'follow_up',
                'proposal',
                'thank_you',
                'custom'
            ])->default('custom');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_email_templates');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
