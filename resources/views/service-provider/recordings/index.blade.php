@extends('service-provider.layouts.app')

@section('title', 'Recording Sessions - Service Provider Portal')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Recording Sessions</h1>
        <p class="mt-1 text-gray-600">Manage your recording sessions</p>
    </div>
    <a href="{{ route('service-provider.recordings.create') }}"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        New Session
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-48">
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="flex-1 min-w-48">
            <select name="subject_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }} (Form {{ $subject->form }})
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Filter
        </button>
        @if(request()->hasAny(['status', 'subject_id']))
            <a href="{{ route('service-provider.recordings') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                Clear
            </a>
        @endif
    </form>
</div>

<!-- Sessions Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject / Topic</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recording</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($sessions as $session)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $session->subject->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $session->topic->name ?? 'No topic' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($session->clock_in)
                            <div>In: {{ $session->clock_in->format('M j, H:i') }}</div>
                            @if($session->clock_out)
                                <div>Out: {{ $session->clock_out->format('H:i') }}</div>
                                <div class="text-indigo-600">{{ $session->total_minutes }} min</div>
                            @endif
                        @else
                            <span class="text-gray-400">Not started</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($session->recording_minutes)
                            {{ $session->recording_minutes }} min
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $session->status === 'approved' ? 'bg-green-100 text-green-800' :
                               ($session->status === 'pending_review' ? 'bg-yellow-100 text-yellow-800' :
                               ($session->status === 'rejected' ? 'bg-red-100 text-red-800' :
                               ($session->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))) }}">
                            {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('service-provider.recordings.show', $session) }}"
                           class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No recording sessions found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection
