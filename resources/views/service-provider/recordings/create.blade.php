@extends('service-provider.layouts.app')

@section('title', 'New Recording Session - Service Provider Portal')

@section('content')
<div class="mb-6">
    <a href="{{ route('service-provider.recordings') }}" class="text-indigo-600 hover:text-indigo-500">
        &larr; Back to Sessions
    </a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900">New Recording Session</h1>
    <p class="mt-1 text-gray-600">Select the subject and topic you will be recording</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('service-provider.recordings') }}" class="space-y-6">
        @csrf

        <div>
            <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject *</label>
            <select name="subject_id" id="subject_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select a subject</option>
                @foreach($subjects->groupBy('form') as $form => $formSubjects)
                    <optgroup label="Form {{ $form }}">
                        @foreach($formSubjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div>
            <label for="topic_id" class="block text-sm font-medium text-gray-700">Topic (Optional)</label>
            <select name="topic_id" id="topic_id"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select a topic (after selecting subject)</option>
            </select>
        </div>

        <div>
            <label for="lesson_plan_id" class="block text-sm font-medium text-gray-700">Lesson Plan (Optional)</label>
            <select name="lesson_plan_id" id="lesson_plan_id"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select an approved lesson plan</option>
                @foreach($lessonPlans as $plan)
                    <option value="{{ $plan->id }}" {{ old('lesson_plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->title }} ({{ $plan->topic->subject->name ?? 'N/A' }} - {{ $plan->topic->name ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
            <textarea name="notes" id="notes" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Any notes about this session">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('service-provider.recordings') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Create Session
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('subject_id').addEventListener('change', function() {
    const subjectId = this.value;
    const topicSelect = document.getElementById('topic_id');

    topicSelect.innerHTML = '<option value="">Loading...</option>';

    if (subjectId) {
        fetch(`/service-provider/api/subjects/${subjectId}/topics`)
            .then(response => response.json())
            .then(topics => {
                topicSelect.innerHTML = '<option value="">Select a topic</option>';
                topics.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = `Term ${topic.term}, Week ${topic.week}: ${topic.name}`;
                    topicSelect.appendChild(option);
                });
            })
            .catch(() => {
                topicSelect.innerHTML = '<option value="">Error loading topics</option>';
            });
    } else {
        topicSelect.innerHTML = '<option value="">Select a topic (after selecting subject)</option>';
    }
});
</script>
@endsection
