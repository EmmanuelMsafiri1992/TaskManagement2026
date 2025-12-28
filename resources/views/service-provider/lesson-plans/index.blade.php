@extends('service-provider.layouts.app')

@section('title', 'Lesson Plans - Service Provider Portal')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Lesson Plans</h1>
        <p class="mt-1 text-gray-600">Create and manage your lesson plans</p>
    </div>
    <a href="{{ route('service-provider.lesson-plans.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        New Lesson Plan
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-48">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Filter
        </button>
        @if(request('status'))
            <a href="{{ route('service-provider.lesson-plans') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                Clear
            </a>
        @endif
    </form>
</div>

<!-- Plans Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject / Topic</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($plans as $plan)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $plan->title }}</div>
                        <div class="text-sm text-gray-500">{{ $plan->created_at->format('M j, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900">{{ $plan->topic->subject->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $plan->topic->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $plan->duration_minutes ? $plan->duration_minutes . ' min' : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $plan->status === 'approved' ? 'bg-green-100 text-green-800' :
                               ($plan->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' :
                               ($plan->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('service-provider.lesson-plans.show', $plan) }}"
                           class="text-indigo-600 hover:text-indigo-900">View</a>
                        @if(in_array($plan->status, ['draft', 'rejected']))
                            <a href="{{ route('service-provider.lesson-plans.edit', $plan) }}"
                               class="ml-3 text-gray-600 hover:text-gray-900">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No lesson plans found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($plans->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $plans->links() }}
        </div>
    @endif
</div>
@endsection
