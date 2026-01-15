@extends('service-provider.layouts.app')

@section('title', 'New Lesson Plan - Service Provider Portal')

@section('content')
<div class="mb-6">
    <a href="{{ route('service-provider.lesson-plans') }}" class="text-indigo-600 hover:text-indigo-500">
        &larr; Back to Lesson Plans
    </a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900">New Lesson Plan</h1>
    <p class="mt-1 text-gray-600">Create a detailed lesson plan for approval</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('service-provider.lesson-plans') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject *</label>
                <select name="subject_id" id="subject_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" data-topics="{{ $subject->topics->toJson() }}">
                            Form {{ $subject->form }} - {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="topic_id" class="block text-sm font-medium text-gray-700">Topic *</label>
                <select name="topic_id" id="topic_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select a topic (after selecting subject)</option>
                </select>
            </div>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Lesson Title *</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
            <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 45) }}"
                   min="1" class="mt-1 block w-32 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="objectives" class="block text-sm font-medium text-gray-700">Learning Objectives</label>
            <textarea name="objectives" id="objectives" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="What will students learn from this lesson?">{{ old('objectives') }}</textarea>
        </div>

        <div>
            <label for="introduction" class="block text-sm font-medium text-gray-700">Introduction</label>
            <textarea name="introduction" id="introduction" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="How will you introduce the topic?">{{ old('introduction') }}</textarea>
        </div>

        <div>
            <label for="main_content" class="block text-sm font-medium text-gray-700">Main Content</label>
            <textarea name="main_content" id="main_content" rows="6"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="The main teaching content and explanations">{{ old('main_content') }}</textarea>
        </div>

        <div>
            <label for="activities" class="block text-sm font-medium text-gray-700">Activities</label>
            <textarea name="activities" id="activities" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Student activities and exercises">{{ old('activities') }}</textarea>
        </div>

        <div>
            <label for="assessment" class="block text-sm font-medium text-gray-700">Assessment</label>
            <textarea name="assessment" id="assessment" rows="3"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="How will you assess student understanding?">{{ old('assessment') }}</textarea>
        </div>

        <div>
            <label for="conclusion" class="block text-sm font-medium text-gray-700">Conclusion</label>
            <textarea name="conclusion" id="conclusion" rows="2"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Summary and wrap-up">{{ old('conclusion') }}</textarea>
        </div>

        <div>
            <label for="homework" class="block text-sm font-medium text-gray-700">Homework</label>
            <textarea name="homework" id="homework" rows="2"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Homework assignment (if any)">{{ old('homework') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-4 pt-4 border-t">
            <a href="{{ route('service-provider.lesson-plans') }}" class="text-gray-600 hover:text-gray-800">Cancel</a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Create Lesson Plan
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('subject_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const topicSelect = document.getElementById('topic_id');

    topicSelect.innerHTML = '<option value="">Select a topic</option>';

    if (selectedOption.value) {
        const topics = JSON.parse(selectedOption.dataset.topics || '[]');
        topics.forEach(topic => {
            const option = document.createElement('option');
            option.value = topic.id;
            option.textContent = `Term ${topic.term}, Week ${topic.week}: ${topic.name}`;
            topicSelect.appendChild(option);
        });
    }
});
</script>
@endsection
