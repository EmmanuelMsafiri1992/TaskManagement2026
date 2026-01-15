@extends('service-provider.layouts.app')

@section('title', 'Dashboard - Service Provider Portal')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $provider->name }}</h1>
    <p class="mt-1 text-gray-600">Here's an overview of your activity and payments</p>
</div>

<!-- Payment Summary Card -->
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <div class="text-indigo-200 text-sm font-medium">Contract Amount</div>
            <div class="mt-1 text-2xl font-bold">MK {{ number_format($provider->total_agreed_amount ?? 700000, 2) }}</div>
        </div>
        <div>
            <div class="text-indigo-200 text-sm font-medium">Total Paid</div>
            <div class="mt-1 text-2xl font-bold">MK {{ number_format($provider->total_paid ?? 0, 2) }}</div>
        </div>
        <div>
            <div class="text-indigo-200 text-sm font-medium">Balance Remaining</div>
            <div class="mt-1 text-2xl font-bold">MK {{ number_format($provider->balance_remaining ?? ($provider->total_agreed_amount ?? 700000), 2) }}</div>
        </div>
        <div>
            <div class="text-indigo-200 text-sm font-medium">Payment Progress</div>
            @php
                $progress = ($provider->total_agreed_amount ?? 700000) > 0
                    ? round((($provider->total_paid ?? 0) / ($provider->total_agreed_amount ?? 700000)) * 100)
                    : 0;
            @endphp
            <div class="mt-2">
                <div class="flex justify-between text-xs mb-1">
                    <span>{{ $progress }}% Complete</span>
                </div>
                <div class="w-full bg-indigo-400 rounded-full h-2.5">
                    <div class="bg-white h-2.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-indigo-400 flex justify-between items-center">
        <div class="text-sm">
            <span class="text-indigo-200">Payment Preference:</span>
            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $provider->payment_preference ?? 'Monthly')) }}</span>
            @if($provider->payment_preference === 'monthly' && $provider->monthly_amount)
                <span class="text-indigo-200 ml-2">| Monthly Amount:</span>
                <span class="font-medium">MK {{ number_format($provider->monthly_amount, 2) }}</span>
            @endif
        </div>
        <a href="{{ route('service-provider.payments') }}" class="bg-white text-indigo-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-50 transition">
            View Payment History
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Total Recording Sessions</div>
        <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $stats['total_sessions'] }}</div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Approved Sessions</div>
        <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['approved_sessions'] }}</div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Total Recording Hours</div>
        <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['total_recording_hours'] }}</div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Approved Lesson Plans</div>
        <div class="mt-2 text-3xl font-bold text-purple-600">{{ $stats['approved_lesson_plans'] }}</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="{{ route('service-provider.recordings.create') }}"
       class="bg-indigo-600 text-white rounded-lg shadow p-6 hover:bg-indigo-700 transition">
        <div class="text-lg font-semibold">Start New Recording Session</div>
        <div class="mt-1 text-indigo-200">Clock in and begin recording lessons</div>
    </a>

    <a href="{{ route('service-provider.lesson-plans.create') }}"
       class="bg-purple-600 text-white rounded-lg shadow p-6 hover:bg-purple-700 transition">
        <div class="text-lg font-semibold">Create Lesson Plan</div>
        <div class="mt-1 text-purple-200">Prepare a new lesson plan for review</div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Recording Sessions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Recording Sessions</h2>
                <a href="{{ route('service-provider.recordings') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View All</a>
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($recentSessions as $session)
                <a href="{{ route('service-provider.recordings.show', $session) }}" class="block px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $session->subject->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $session->topic->name ?? 'No topic' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $session->status === 'approved' ? 'bg-green-100 text-green-800' :
                                   ($session->status === 'pending_review' ? 'bg-yellow-100 text-yellow-800' :
                                   ($session->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ $session->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recording sessions yet
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Lesson Plans -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Lesson Plans</h2>
                <a href="{{ route('service-provider.lesson-plans') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View All</a>
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($recentPlans as $plan)
                <a href="{{ route('service-provider.lesson-plans.show', $plan) }}" class="block px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $plan->title }}</div>
                            <div class="text-sm text-gray-500">{{ $plan->topic->subject->name ?? 'N/A' }} - {{ $plan->topic->name ?? 'N/A' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $plan->status === 'approved' ? 'bg-green-100 text-green-800' :
                                   ($plan->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' :
                                   ($plan->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ $plan->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No lesson plans yet
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Your Information Card -->
<div class="mt-8 bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Your Information</h2>
        <a href="{{ route('service-provider.profile') }}" class="text-sm text-indigo-600 hover:text-indigo-500">Edit Profile</a>
    </div>
    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <dt class="text-sm font-medium text-gray-500">Specialty</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $provider->specialty ?? 'Not specified' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Qualification</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $provider->qualification ?? 'Not specified' }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $provider->status === 'active' ? 'bg-green-100 text-green-800' :
                       ($provider->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst($provider->status) }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
            <dd class="mt-1 text-sm text-gray-900">
                @if($provider->payment_method === 'bank')
                    Bank Transfer ({{ $provider->bank_name ?? 'Not set' }})
                @elseif($provider->payment_method === 'mobile_money')
                    Mobile Money ({{ $provider->mobile_money_provider ?? 'Not set' }})
                @else
                    Not specified
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Total Work Hours</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $stats['total_work_hours'] }} hours</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Pending Sessions</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $stats['pending_sessions'] }}</dd>
        </div>
    </dl>
</div>

<!-- Agreement Download (if signed) -->
@if($provider->agreement_signed)
<div class="mt-6 bg-green-50 rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium text-green-800">Service Agreement</h3>
            <p class="text-sm text-green-600">Your agreement was signed on {{ $provider->agreement_signed_at?->format('M j, Y') ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('service-provider.download-agreement') }}"
           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Agreement
        </a>
    </div>
</div>
@endif
@endsection
