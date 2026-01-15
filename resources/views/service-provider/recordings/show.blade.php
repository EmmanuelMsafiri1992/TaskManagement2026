@extends('service-provider.layouts.app')

@section('title', 'Recording Session - Service Provider Portal')

@section('content')
<div class="mb-6">
    <a href="{{ route('service-provider.recordings') }}" class="text-indigo-600 hover:text-indigo-500">
        &larr; Back to Sessions
    </a>
    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Recording Session</h1>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            {{ $session->status === 'approved' ? 'bg-green-100 text-green-800' :
               ($session->status === 'pending_review' ? 'bg-yellow-100 text-yellow-800' :
               ($session->status === 'rejected' ? 'bg-red-100 text-red-800' :
               ($session->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))) }}">
            {{ ucfirst(str_replace('_', ' ', $session->status)) }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Session Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Session Details</h2>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Subject</dt>
                    <dd class="mt-1 text-gray-900">{{ $session->subject->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Form</dt>
                    <dd class="mt-1 text-gray-900">Form {{ $session->subject->form ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Topic</dt>
                    <dd class="mt-1 text-gray-900">{{ $session->topic->name ?? 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Lesson Plan</dt>
                    <dd class="mt-1 text-gray-900">{{ $session->lessonPlan->title ?? 'Not linked' }}</dd>
                </div>
            </dl>

            @if($session->notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-gray-900">{{ $session->notes }}</dd>
                </div>
            @endif

            @if($session->admin_notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <dt class="text-sm font-medium text-gray-500">Admin Feedback</dt>
                    <dd class="mt-1 text-red-600">{{ $session->admin_notes }}</dd>
                </div>
            @endif
        </div>

        <!-- Time Tracking -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Tracking</h2>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Clock In</div>
                    <div class="text-xl font-semibold text-gray-900">
                        {{ $session->clock_in ? $session->clock_in->format('M j, Y H:i') : 'Not yet' }}
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-500">Clock Out</div>
                    <div class="text-xl font-semibold text-gray-900">
                        {{ $session->clock_out ? $session->clock_out->format('M j, Y H:i') : 'Not yet' }}
                    </div>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-blue-600">Total Work Time</div>
                    <div class="text-xl font-semibold text-blue-900">
                        {{ $session->total_minutes ?? 0 }} minutes
                    </div>
                </div>
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <div class="text-sm text-indigo-600">Recording Time</div>
                    <div class="text-xl font-semibold text-indigo-900">
                        {{ $session->recording_minutes ?? 0 }} minutes
                    </div>
                </div>
            </div>

            @if($session->quality_rating)
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-green-600">Quality Rating</div>
                    <div class="text-xl font-semibold text-green-900">
                        {{ $session->quality_rating }}/5
                    </div>
                </div>
            @endif
        </div>

        <!-- Video Upload -->
        @if(in_array($session->status, ['completed', 'pending_review']))
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Video File</h2>

                @if($session->video_file)
                    <div class="p-4 bg-green-50 rounded-lg mb-4">
                        <p class="text-green-700">Video uploaded successfully</p>
                        <a href="{{ Storage::url($session->video_file) }}" target="_blank"
                           class="text-indigo-600 hover:text-indigo-500">View Video</a>
                    </div>
                @endif

                <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/upload-video"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <input type="file" name="video" accept="video/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Upload Video
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>

            <div class="space-y-3">
                @if($session->status === 'draft')
                    @if(!$session->clock_in)
                        <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/clock-in">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                                Clock In
                            </button>
                        </form>
                    @endif
                @endif

                @if($session->status === 'in_progress')
                    @if(!$session->recording_start)
                        <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/start-recording">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                Start Recording
                            </button>
                        </form>
                    @elseif(!$session->recording_end)
                        <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/stop-recording">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                                Stop Recording
                            </button>
                        </form>
                    @endif

                    @if(!$session->clock_out)
                        <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/clock-out">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                                Clock Out
                            </button>
                        </form>
                    @endif
                @endif

                @if($session->status === 'completed')
                    <form method="POST" action="{{ route('service-provider.recordings') }}/{{ $session->id }}/submit">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                            Submit for Review
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Session Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>

            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Session Created</p>
                        <p class="text-xs text-gray-500">{{ $session->created_at->format('M j, Y H:i') }}</p>
                    </div>
                </div>

                @if($session->clock_in)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Clocked In</p>
                            <p class="text-xs text-gray-500">{{ $session->clock_in->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($session->recording_start)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="6"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Recording Started</p>
                            <p class="text-xs text-gray-500">{{ $session->recording_start->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($session->recording_end)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Recording Stopped</p>
                            <p class="text-xs text-gray-500">{{ $session->recording_end->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($session->clock_out)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Clocked Out</p>
                            <p class="text-xs text-gray-500">{{ $session->clock_out->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                @if($session->approved_at)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 {{ $session->status === 'approved' ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                            @if($session->status === 'approved')
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($session->status) }}</p>
                            <p class="text-xs text-gray-500">{{ $session->approved_at->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
