@extends('service-provider.layouts.app')

@section('title', 'Lesson Plan - Service Provider Portal')

@section('content')
<div class="mb-6">
    <a href="{{ route('service-provider.lesson-plans') }}" class="text-indigo-600 hover:text-indigo-500">
        &larr; Back to Lesson Plans
    </a>
    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ $plan->title }}</h1>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            {{ $plan->status === 'approved' ? 'bg-green-100 text-green-800' :
               ($plan->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' :
               ($plan->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
            {{ ucfirst($plan->status) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-sm text-gray-500">Subject</span>
                    <p class="font-medium">{{ $plan->topic->subject->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Topic</span>
                    <p class="font-medium">{{ $plan->topic->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Form</span>
                    <p class="font-medium">Form {{ $plan->topic->subject->form ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Duration</span>
                    <p class="font-medium">{{ $plan->duration_minutes ?? '-' }} minutes</p>
                </div>
            </div>

            @if($plan->objectives)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Learning Objectives</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->objectives }}</div>
                </div>
            @endif

            @if($plan->introduction)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Introduction</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->introduction }}</div>
                </div>
            @endif

            @if($plan->main_content)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Main Content</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->main_content }}</div>
                </div>
            @endif

            @if($plan->activities)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Activities</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->activities }}</div>
                </div>
            @endif

            @if($plan->assessment)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Assessment</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->assessment }}</div>
                </div>
            @endif

            @if($plan->conclusion)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Conclusion</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->conclusion }}</div>
                </div>
            @endif

            @if($plan->homework)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Homework</h3>
                    <div class="text-gray-900 whitespace-pre-wrap">{{ $plan->homework }}</div>
                </div>
            @endif
        </div>

        @if($plan->feedback)
            <div class="bg-red-50 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-red-800 mb-2">Feedback</h3>
                <div class="text-red-700">{{ $plan->feedback }}</div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>

            <div class="space-y-3">
                @if(in_array($plan->status, ['draft', 'rejected']))
                    <a href="{{ route('service-provider.lesson-plans.edit', $plan) }}"
                       class="block w-full px-4 py-2 text-center bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Edit Plan
                    </a>
                    <form method="POST" action="{{ route('service-provider.lesson-plans') }}/{{ $plan->id }}/submit">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Submit for Review
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-gray-500">Created</dt>
                    <dd class="text-gray-900">{{ $plan->created_at->format('M j, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Last Updated</dt>
                    <dd class="text-gray-900">{{ $plan->updated_at->format('M j, Y H:i') }}</dd>
                </div>
                @if($plan->approved_at)
                    <div>
                        <dt class="text-sm text-gray-500">{{ $plan->status === 'approved' ? 'Approved' : 'Reviewed' }} At</dt>
                        <dd class="text-gray-900">{{ $plan->approved_at->format('M j, Y H:i') }}</dd>
                    </div>
                    @if($plan->approvedBy)
                        <div>
                            <dt class="text-sm text-gray-500">Reviewed By</dt>
                            <dd class="text-gray-900">{{ $plan->approvedBy->name }}</dd>
                        </div>
                    @endif
                @endif
            </dl>
        </div>

        @if($plan->recordingSessions->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recording Sessions</h2>
                <div class="space-y-2">
                    @foreach($plan->recordingSessions as $session)
                        <a href="{{ route('service-provider.recordings.show', $session) }}"
                           class="block p-3 bg-gray-50 rounded hover:bg-gray-100">
                            <div class="text-sm font-medium">{{ $session->created_at->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
