@extends('service-provider.layouts.app')

@section('title', 'Profile - Service Provider Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
    <p class="mt-1 text-gray-600">Manage your profile and payment information</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
@endif

<!-- Daily Payment Setup Modal -->
<div id="dailyPaymentSetupModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDailyPaymentModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Daily Payment Setup
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Please provide the following information to set up your daily payment plan. This will help us calculate how many topics you need to complete per day.
                        </p>

                        <div class="mt-4 space-y-4">
                            <!-- Number of Subjects -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">How many subjects will you be teaching?</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative border rounded-lg p-3 cursor-pointer hover:border-indigo-400 transition-all border-indigo-500 bg-indigo-50" id="subject-count-1-label">
                                        <input type="radio" name="modal_subject_count" value="1" class="sr-only" onchange="updateSubjectCount(1)">
                                        <div class="text-center">
                                            <div class="font-bold text-lg text-indigo-600">1 Subject</div>
                                            <div class="text-sm text-gray-600">MK 350,000</div>
                                        </div>
                                    </label>
                                    <label class="relative border rounded-lg p-3 cursor-pointer hover:border-indigo-400 transition-all border-gray-300" id="subject-count-2-label">
                                        <input type="radio" name="modal_subject_count" value="2" class="sr-only" onchange="updateSubjectCount(2)" checked>
                                        <div class="text-center">
                                            <div class="font-bold text-lg text-indigo-600">2 Subjects</div>
                                            <div class="text-sm text-gray-600">MK 700,000</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Starting Subject -->
                            <div>
                                <label for="modal_starting_subject" class="block text-sm font-medium text-gray-700">Subject you are starting with *</label>
                                <select id="modal_starting_subject" name="modal_starting_subject" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        onchange="onSubjectSelected()">
                                    <option value="">Select Subject ({{ count($subjects) }} available)</option>
                                    @forelse($subjects as $subject)
                                        <option value="{{ $subject['name'] }}" data-topics="{{ $subject['total_topics'] }}">
                                            {{ $subject['name'] }} (Forms 1-4) - {{ $subject['total_topics'] }} topics
                                        </option>
                                    @empty
                                        <option value="" disabled>No subjects found in database</option>
                                    @endforelse
                                </select>
                                <p class="mt-1 text-xs text-gray-500">This covers all topics for the subject across Forms 1 to 4</p>
                            </div>

                            <!-- Total Topics -->
                            <div>
                                <label for="modal_total_topics" class="block text-sm font-medium text-gray-700">Total topics according to syllabus *</label>
                                <input type="number" id="modal_total_topics" name="modal_total_topics" min="1" required
                                       placeholder="Auto-filled from selected subject"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       oninput="calculateModalRequirements()">
                                <p class="mt-1 text-xs text-gray-500">This is auto-filled when you select a subject. You can adjust if your syllabus differs.</p>
                            </div>

                            <!-- Calculation Result -->
                            <div id="modal-calculation-result" class="hidden bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-4">
                                <h4 class="font-medium text-indigo-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Your Daily Requirements
                                </h4>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                                        <span class="text-xs text-gray-500 block">Total Budget</span>
                                        <span class="font-bold text-lg text-gray-900" id="modal-total-budget">MK 700,000.00</span>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                                        <span class="text-xs text-gray-500 block">Your Daily Rate</span>
                                        <span class="font-bold text-lg text-gray-900" id="modal-daily-rate">MK 0.00</span>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                                        <span class="text-xs text-gray-500 block">Max Payable Days</span>
                                        <span class="font-bold text-lg text-indigo-600" id="modal-max-days">0 days</span>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                                        <span class="text-xs text-gray-500 block">Total Topics</span>
                                        <span class="font-bold text-lg text-gray-900" id="modal-total-topics">0 topics</span>
                                    </div>
                                </div>

                                <div class="bg-indigo-100 rounded-lg p-4 text-center">
                                    <p class="text-indigo-800 font-medium text-sm">Topics You Must Complete Per Day</p>
                                    <p class="text-4xl font-bold text-indigo-600 my-2" id="modal-topics-per-day">0</p>
                                    <p class="text-sm text-indigo-700" id="modal-summary-text">
                                        Complete at least <span id="modal-topics-count">0</span> topic(s) per day to receive your daily payment
                                    </p>
                                </div>

                                <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    <p class="text-xs text-amber-700 mb-2">
                                        <strong>How it works:</strong>
                                    </p>
                                    <ul class="text-xs text-amber-700 list-disc list-inside space-y-1">
                                        <li>Your budget covers all topics for this subject (Forms 1-4)</li>
                                        <li>Max Payable Days = Total Budget / Daily Rate</li>
                                        <li>Topics Per Day = Total Topics / Max Payable Days</li>
                                        <li>Complete the required topics each day to receive your daily payment</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="saveDailySetupBtn" onclick="saveDailyPaymentSetup()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    Save & Continue
                </button>
                <button type="button" onclick="closeDailyPaymentModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Form -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
            <form method="POST" action="{{ route('service-provider.profile') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $provider->name) }}" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" value="{{ $provider->email }}" disabled
                               class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                        <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $provider->phone) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="national_id" class="block text-sm font-medium text-gray-700">National ID</label>
                        <input type="text" id="national_id" value="{{ $provider->national_id }}" disabled
                               class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $provider->address) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="specialty" class="block text-sm font-medium text-gray-700">Subject Specialty</label>
                        <input type="text" name="specialty" id="specialty" value="{{ old('specialty', $provider->specialty) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="qualification" class="block text-sm font-medium text-gray-700">Qualification</label>
                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification', $provider->qualification) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea name="bio" id="bio" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Tell us about yourself">{{ old('bio', $provider->bio) }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Preferences -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Preferences</h2>
            <form method="POST" action="{{ route('service-provider.payment-settings') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-indigo-800">Contract Amount</h3>
                            <p class="text-sm text-indigo-600">Your total agreed contract value ({{ $provider->assigned_subjects_count ?? 1 }} subject{{ ($provider->assigned_subjects_count ?? 1) > 1 ? 's' : '' }})</p>
                        </div>
                        <div class="text-2xl font-bold text-indigo-600" id="contract-amount-display">MK {{ number_format($provider->total_agreed_amount ?? 350000, 2) }}</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Payment Preference</label>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ ($provider->payment_preference ?? 'bi_weekly') === 'bi_weekly' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="payment_preference" value="bi_weekly" class="sr-only"
                                   {{ ($provider->payment_preference ?? 'bi_weekly') === 'bi_weekly' ? 'checked' : '' }}
                                   onchange="togglePaymentPreference()">
                            <div class="font-medium text-gray-900">Bi-Weekly</div>
                            <div class="text-sm text-gray-500">Every 2 weeks</div>
                        </label>
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ $provider->payment_preference === 'monthly' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="payment_preference" value="monthly" class="sr-only"
                                   {{ $provider->payment_preference === 'monthly' ? 'checked' : '' }}
                                   onchange="togglePaymentPreference()">
                            <div class="font-medium text-gray-900">Monthly</div>
                            <div class="text-sm text-gray-500">Each month</div>
                        </label>
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ $provider->payment_preference === 'daily' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="payment_preference" value="daily" class="sr-only"
                                   {{ $provider->payment_preference === 'daily' ? 'checked' : '' }}
                                   onchange="togglePaymentPreference()">
                            <div class="font-medium text-gray-900">Daily</div>
                            <div class="text-sm text-gray-500">Per day rate</div>
                        </label>
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ $provider->payment_preference === 'lump_sum' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="payment_preference" value="lump_sum" class="sr-only"
                                   {{ $provider->payment_preference === 'lump_sum' ? 'checked' : '' }}
                                   onchange="togglePaymentPreference()">
                            <div class="font-medium text-gray-900">Lump Sum</div>
                            <div class="text-sm text-gray-500">After completion</div>
                        </label>
                    </div>
                </div>

                <div id="monthly-amount-field" class="{{ in_array($provider->payment_preference, ['lump_sum', 'daily']) ? 'hidden' : '' }}">
                    <label for="monthly_amount" id="amount-label" class="block text-sm font-medium text-gray-700">Preferred {{ $provider->payment_preference === 'bi_weekly' ? 'Bi-Weekly' : 'Monthly' }} Amount (MK)</label>
                    <input type="number" name="monthly_amount" id="monthly_amount"
                           value="{{ old('monthly_amount', $provider->monthly_amount) }}"
                           placeholder="e.g., 100000"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p id="amount-hint" class="mt-1 text-xs text-gray-500">How much would you like to receive {{ $provider->payment_preference === 'bi_weekly' ? 'every 2 weeks' : 'per month' }}?</p>
                </div>

                <!-- Daily Rate Fields -->
                <div id="daily-rate-field" class="{{ $provider->payment_preference !== 'daily' ? 'hidden' : '' }}">
                    <label for="daily_rate" class="block text-sm font-medium text-gray-700">Daily Rate (MK)</label>
                    <input type="number" name="daily_rate" id="daily_rate"
                           value="{{ old('daily_rate', $provider->daily_rate) }}"
                           placeholder="e.g., 40000"
                           min="1000"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           onchange="calculateDailyRequirements()">
                    <p class="mt-1 text-xs text-gray-500">How much do you want to be paid per day?</p>

                    <!-- Daily Rate Calculator Info -->
                    <div id="daily-rate-info" class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 {{ !$provider->daily_rate ? 'hidden' : '' }}">
                        <h4 class="font-medium text-amber-800 mb-2">Daily Payment Requirements</h4>

                        @if($provider->daily_payment_setup_complete && $provider->daily_subject_name)
                        <div class="mb-3 bg-white rounded-lg p-3 border border-amber-100">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Subject:</span>
                                <span class="font-medium text-gray-900">{{ $provider->daily_subject_name }} (Forms 1-4)</span>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-1">
                                <span class="text-gray-600">Number of Subjects:</span>
                                <span class="font-medium text-gray-900">{{ $provider->assigned_subjects_count }} subject(s) @ MK 350,000 each</span>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-1">
                                <span class="text-gray-600">Total Topics (Syllabus):</span>
                                <span class="font-medium text-gray-900">{{ $provider->daily_total_topics }} topics</span>
                            </div>
                        </div>
                        @endif

                        <p class="text-sm text-amber-700 mb-3">
                            Based on your daily rate, here's what you need to complete to ensure full payment:
                        </p>

                        @php
                            $totalTopics = $provider->daily_total_topics ?? ($provider->getTotalTopicsCount() ?: 100);
                            $amountPerSubject = $provider->amount_per_subject ?? 350000;
                            $subjectCount = $provider->assigned_subjects_count ?? 1;
                            $dailyRate = $provider->daily_rate ?? 40000;
                            $totalBudget = $amountPerSubject * $subjectCount;
                            $maxDays = $dailyRate > 0 ? floor($totalBudget / $dailyRate) : 0;
                            $topicsPerDay = $maxDays > 0 ? ceil($totalTopics / $maxDays) : 0;
                        @endphp

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-white rounded p-3">
                                <span class="text-gray-600 block">Total Budget</span>
                                <span class="font-bold text-lg text-gray-900" id="calc-total-budget">MK {{ number_format($totalBudget, 2) }}</span>
                            </div>
                            <div class="bg-white rounded p-3">
                                <span class="text-gray-600 block">Your Daily Rate</span>
                                <span class="font-bold text-lg text-gray-900" id="calc-daily-rate">MK {{ number_format($dailyRate, 2) }}</span>
                            </div>
                            <div class="bg-white rounded p-3">
                                <span class="text-gray-600 block">Max Payable Days</span>
                                <span class="font-bold text-lg text-indigo-600" id="calc-max-days">{{ $maxDays }} days</span>
                            </div>
                            <div class="bg-white rounded p-3">
                                <span class="text-gray-600 block">Total Topics</span>
                                <span class="font-bold text-lg text-gray-900" id="calc-total-topics">{{ $totalTopics }} topics</span>
                            </div>
                        </div>

                        <div class="mt-4 bg-indigo-100 rounded-lg p-4 text-center">
                            <p class="text-indigo-800 font-medium">Required Topics Per Day</p>
                            <p class="text-3xl font-bold text-indigo-600" id="calc-topics-per-day">{{ $topicsPerDay }}</p>
                            <p class="text-sm text-indigo-700 mt-1">
                                You must complete at least <span id="calc-topics-per-day-text">{{ $topicsPerDay }}</span> topic(s) per day to receive your daily payment of <span id="calc-daily-rate-text">MK {{ number_format($dailyRate, 2) }}</span>
                            </p>
                        </div>

                        <div class="mt-3 text-xs text-amber-600">
                            <strong>Note:</strong> This ensures you complete all topics before exhausting your budget. If you don't complete the required topics, payment may be withheld until you catch up.
                        </div>

                        <div class="mt-4 pt-3 border-t border-amber-200">
                            <button type="button" onclick="openDailyPaymentModal()"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Update Subject & Topics Setup
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Payment Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Method -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
            <form method="POST" action="{{ route('service-provider.payment-method') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Receive payments via</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ ($provider->payment_method ?? 'bank') === 'bank' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}" id="bank-method-label">
                            <input type="radio" name="payment_method" value="bank" class="sr-only"
                                   {{ ($provider->payment_method ?? 'bank') === 'bank' ? 'checked' : '' }}
                                   onchange="togglePaymentMethodFields()">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <div class="font-medium text-gray-900">Bank Transfer</div>
                            </div>
                        </label>
                        <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all {{ $provider->payment_method === 'mobile_money' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}" id="mobile-method-label">
                            <input type="radio" name="payment_method" value="mobile_money" class="sr-only"
                                   {{ $provider->payment_method === 'mobile_money' ? 'checked' : '' }}
                                   onchange="togglePaymentMethodFields()">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div class="font-medium text-gray-900">Mobile Money</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Bank Details -->
                <div id="bank-fields" class="{{ $provider->payment_method === 'mobile_money' ? 'hidden' : '' }} bg-gray-50 rounded-lg p-4 space-y-4">
                    <h4 class="font-medium text-gray-900">Bank Account Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                            <select name="bank_name" id="bank_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Bank</option>
                                <option value="National Bank of Malawi" {{ $provider->bank_name == 'National Bank of Malawi' ? 'selected' : '' }}>National Bank of Malawi</option>
                                <option value="Standard Bank" {{ $provider->bank_name == 'Standard Bank' ? 'selected' : '' }}>Standard Bank</option>
                                <option value="FDH Bank" {{ $provider->bank_name == 'FDH Bank' ? 'selected' : '' }}>FDH Bank</option>
                                <option value="NBS Bank" {{ $provider->bank_name == 'NBS Bank' ? 'selected' : '' }}>NBS Bank</option>
                                <option value="Ecobank" {{ $provider->bank_name == 'Ecobank' ? 'selected' : '' }}>Ecobank</option>
                                <option value="First Capital Bank" {{ $provider->bank_name == 'First Capital Bank' ? 'selected' : '' }}>First Capital Bank</option>
                                <option value="CDH Bank" {{ $provider->bank_name == 'CDH Bank' ? 'selected' : '' }}>CDH Bank</option>
                                <option value="MyBucks" {{ $provider->bank_name == 'MyBucks' ? 'selected' : '' }}>MyBucks</option>
                            </select>
                        </div>
                        <div>
                            <label for="bank_branch" class="block text-sm font-medium text-gray-700">Branch</label>
                            <input type="text" name="bank_branch" id="bank_branch" value="{{ old('bank_branch', $provider->bank_branch) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="bank_account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                            <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $provider->bank_account_name) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="bank_account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                            <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $provider->bank_account_number) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Mobile Money Details -->
                <div id="mobile-fields" class="{{ ($provider->payment_method ?? 'bank') !== 'mobile_money' ? 'hidden' : '' }} bg-gray-50 rounded-lg p-4 space-y-4">
                    <h4 class="font-medium text-gray-900">Mobile Money Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mobile_money_provider" class="block text-sm font-medium text-gray-700">Provider</label>
                            <select name="mobile_money_provider" id="mobile_money_provider" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Provider</option>
                                <option value="Airtel Money" {{ $provider->mobile_money_provider == 'Airtel Money' ? 'selected' : '' }}>Airtel Money</option>
                                <option value="TNM Mpamba" {{ $provider->mobile_money_provider == 'TNM Mpamba' ? 'selected' : '' }}>TNM Mpamba</option>
                            </select>
                        </div>
                        <div>
                            <label for="mobile_money_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="mobile_money_number" id="mobile_money_number" value="{{ old('mobile_money_number', $provider->mobile_money_number) }}"
                                   placeholder="e.g., 0999123456"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="mobile_money_name" class="block text-sm font-medium text-gray-700">Registered Name</label>
                            <input type="text" name="mobile_money_name" id="mobile_money_name" value="{{ old('mobile_money_name', $provider->mobile_money_name) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Account Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $provider->status === 'active' ? 'bg-green-100 text-green-800' :
                               ($provider->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Agreement</dt>
                    <dd class="text-gray-900">
                        @if($provider->agreement_signed)
                            <span class="text-green-600">Signed</span>
                            @if($provider->agreement_signed_at)
                                <span class="text-xs text-gray-500 block">{{ $provider->agreement_signed_at->format('M j, Y') }}</span>
                            @endif
                        @else
                            <span class="text-yellow-600">Not Signed</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Member Since</dt>
                    <dd class="text-gray-900">{{ $provider->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Payment Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-gray-500">Total Agreed</dt>
                    <dd class="text-lg font-semibold text-gray-900">MK {{ number_format($provider->total_agreed_amount ?? 700000, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Total Paid</dt>
                    <dd class="text-lg font-semibold text-green-600">MK {{ number_format($provider->total_paid ?? 0, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Balance Remaining</dt>
                    <dd class="text-lg font-semibold text-indigo-600">MK {{ number_format($provider->balance_remaining ?? ($provider->total_agreed_amount ?? 700000), 2) }}</dd>
                </div>
            </dl>

            <!-- Payment Progress Bar -->
            <div class="mt-4">
                @php
                    $progress = $provider->total_agreed_amount > 0
                        ? round(($provider->total_paid / $provider->total_agreed_amount) * 100)
                        : 0;
                @endphp
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                    <span>Payment Progress</span>
                    <span>{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>

        <!-- Agreement Download -->
        @if($provider->agreement_signed)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Agreement</h2>
            <p class="text-sm text-gray-600 mb-4">Download your signed service provider agreement.</p>
            <a href="{{ route('service-provider.download-agreement') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 w-full justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Agreement PDF
            </a>
        </div>
        @endif

        <!-- Syllabus Commitment Acknowledgment -->
        @if($provider->agreement_signed)
        <div class="bg-white rounded-lg shadow p-6 {{ !$provider->syllabus_commitment_acknowledged ? 'ring-2 ring-amber-400' : '' }}">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                @if($provider->syllabus_commitment_acknowledged)
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                @endif
                Syllabus Commitment
            </h2>

            @if($provider->syllabus_commitment_acknowledged)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800 font-medium mb-2">Commitment Acknowledged</p>
                    <p class="text-sm text-green-700">
                        You have acknowledged your commitment to complete the entire syllabus for each subject assigned to you.
                    </p>
                    @if($provider->syllabus_commitment_acknowledged_at)
                        <p class="text-xs text-green-600 mt-2">
                            Acknowledged on: {{ $provider->syllabus_commitment_acknowledged_at->format('M j, Y \a\t g:i A') }}
                        </p>
                    @endif
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-amber-800 font-medium mb-2">Action Required</p>
                    <p class="text-sm text-amber-700">
                        Please acknowledge your commitment to completing the <strong>entire syllabus</strong> for each subject assigned to you. This means covering all topics and lessons in full - no partial completion.
                    </p>
                </div>

                <form method="POST" action="{{ route('service-provider.acknowledge-syllabus-commitment') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="acknowledge" id="acknowledge_commitment" required
                                   class="mt-1 h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-gray-700">
                                I understand and commit to completing the <strong class="text-gray-900">entire syllabus</strong> for each subject assigned to me, covering all topics and lessons in full. I acknowledge that partial completion is not acceptable.
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-medium transition-colors">
                        Acknowledge Commitment
                    </button>
                </form>
            @endif
        </div>
        @endif

        @if($provider->status === 'pending')
            <div class="bg-yellow-50 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">Account Pending</h3>
                <p class="text-sm text-yellow-700">
                    Your account is pending approval. You will be notified once an administrator reviews your account.
                </p>
            </div>
        @endif
    </div>
</div>

<script>
// Configuration from server
const dailyRateConfig = {
    totalBudget: {{ ($provider->amount_per_subject ?? 350000) * ($provider->assigned_subjects_count ?? 1) }},
    totalTopics: {{ $provider->daily_total_topics ?? ($provider->getTotalTopicsCount() ?: 100) }},
    amountPerSubject: {{ $provider->amount_per_subject ?? 350000 }},
    subjectCount: {{ $provider->assigned_subjects_count ?? 1 }},
    dailyPaymentSetupComplete: {{ $provider->daily_payment_setup_complete ? 'true' : 'false' }},
    currentDailyRate: {{ $provider->daily_rate ?? 0 }}
};

// Modal state
let modalSubjectCount = 1;

function openDailyPaymentModal() {
    document.getElementById('dailyPaymentSetupModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Reset form with saved values
    document.getElementById('modal_starting_subject').value = '{{ $provider->daily_subject_name ?? '' }}';
    document.getElementById('modal_total_topics').value = '{{ $provider->daily_total_topics ?? '' }}';
    updateSubjectCount({{ $provider->assigned_subjects_count ?? 1 }});
    calculateModalRequirements();
}

function onSubjectSelected() {
    const subjectSelect = document.getElementById('modal_starting_subject');
    const topicsInput = document.getElementById('modal_total_topics');
    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];

    if (selectedOption && selectedOption.dataset.topics) {
        // Auto-populate total topics from the subject's actual topic count
        const totalTopics = parseInt(selectedOption.dataset.topics) || 0;
        topicsInput.value = totalTopics;
        topicsInput.placeholder = `${totalTopics} topics from syllabus`;
    }

    calculateModalRequirements();
}

function closeDailyPaymentModal() {
    document.getElementById('dailyPaymentSetupModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function updateSubjectCount(count) {
    modalSubjectCount = count;

    // Update visual selection
    const label1 = document.getElementById('subject-count-1-label');
    const label2 = document.getElementById('subject-count-2-label');

    if (count === 1) {
        label1.classList.add('border-indigo-500', 'bg-indigo-50');
        label1.classList.remove('border-gray-300');
        label2.classList.remove('border-indigo-500', 'bg-indigo-50');
        label2.classList.add('border-gray-300');
        document.querySelector('input[name="modal_subject_count"][value="1"]').checked = true;
    } else {
        label2.classList.add('border-indigo-500', 'bg-indigo-50');
        label2.classList.remove('border-gray-300');
        label1.classList.remove('border-indigo-500', 'bg-indigo-50');
        label1.classList.add('border-gray-300');
        document.querySelector('input[name="modal_subject_count"][value="2"]').checked = true;
    }

    calculateModalRequirements();
}

function calculateModalRequirements() {
    const subjectSelect = document.getElementById('modal_starting_subject');
    const topicsInput = document.getElementById('modal_total_topics');
    const resultDiv = document.getElementById('modal-calculation-result');
    const saveBtn = document.getElementById('saveDailySetupBtn');

    const subjectId = subjectSelect.value;
    const totalTopics = parseInt(topicsInput.value) || 0;
    const dailyRate = parseFloat(document.getElementById('daily_rate')?.value) || dailyRateConfig.currentDailyRate || 40000;

    // Enable save button if both fields are filled
    if (subjectId && totalTopics > 0) {
        saveBtn.disabled = false;
    } else {
        saveBtn.disabled = true;
        resultDiv.classList.add('hidden');
        return;
    }

    // Calculate values
    const amountPerSubject = 350000;
    const totalBudget = amountPerSubject * modalSubjectCount;
    const maxDays = dailyRate > 0 ? Math.floor(totalBudget / dailyRate) : 0;
    const topicsPerDay = maxDays > 0 ? Math.ceil(totalTopics / maxDays) : 0;

    // Format money
    const formatMoney = (amount) => 'MK ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Update display
    document.getElementById('modal-total-budget').textContent = formatMoney(totalBudget);
    document.getElementById('modal-daily-rate').textContent = formatMoney(dailyRate);
    document.getElementById('modal-max-days').textContent = maxDays + ' days';
    document.getElementById('modal-total-topics').textContent = totalTopics + ' topics';
    document.getElementById('modal-topics-per-day').textContent = topicsPerDay;
    document.getElementById('modal-topics-count').textContent = topicsPerDay;

    // Show result
    resultDiv.classList.remove('hidden');
}

async function saveDailyPaymentSetup() {
    const subjectName = document.getElementById('modal_starting_subject').value;
    const totalTopics = document.getElementById('modal_total_topics').value;
    const saveBtn = document.getElementById('saveDailySetupBtn');

    if (!subjectName || !totalTopics) {
        alert('Please fill in all required fields');
        return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    try {
        const response = await fetch('{{ route("service-provider.daily-payment-setup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                daily_subject_name: subjectName,
                daily_total_topics: parseInt(totalTopics),
                assigned_subjects_count: modalSubjectCount
            })
        });

        const data = await response.json();

        if (data.success) {
            // Update the config
            dailyRateConfig.totalBudget = data.data.total_amount;
            dailyRateConfig.totalTopics = parseInt(totalTopics);
            dailyRateConfig.subjectCount = modalSubjectCount;
            dailyRateConfig.dailyPaymentSetupComplete = true;

            // Update the page display
            document.getElementById('calc-total-budget').textContent = 'MK ' + data.data.total_amount.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('calc-total-topics').textContent = totalTopics + ' topics';

            // Recalculate daily requirements
            calculateDailyRequirements();

            // Close modal
            closeDailyPaymentModal();

            // Show success message
            alert('Daily payment setup saved successfully!\n\nSubject: ' + data.data.subject_name + ' (Forms 1-4)\nTotal Topics: ' + totalTopics + '\nTopics Per Day Required: ' + data.data.topics_per_day);

            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert('Error saving setup: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving setup. Please try again.');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save & Continue';
    }
}

function togglePaymentPreference() {
    const biWeeklyOption = document.querySelector('input[name="payment_preference"][value="bi_weekly"]');
    const monthlyOption = document.querySelector('input[name="payment_preference"][value="monthly"]');
    const dailyOption = document.querySelector('input[name="payment_preference"][value="daily"]');
    const lumpSumOption = document.querySelector('input[name="payment_preference"][value="lump_sum"]');

    const amountField = document.getElementById('monthly-amount-field');
    const dailyRateField = document.getElementById('daily-rate-field');
    const amountLabel = document.getElementById('amount-label');
    const amountHint = document.getElementById('amount-hint');

    // Show/hide amount fields based on selection
    if (amountField) {
        const showMonthlyAmount = (biWeeklyOption && biWeeklyOption.checked) || (monthlyOption && monthlyOption.checked);
        amountField.classList.toggle('hidden', !showMonthlyAmount);

        // Update label and hint based on selection
        if (biWeeklyOption && biWeeklyOption.checked) {
            if (amountLabel) amountLabel.textContent = 'Preferred Bi-Weekly Amount (MK)';
            if (amountHint) amountHint.textContent = 'How much would you like to receive every 2 weeks?';
        } else if (monthlyOption && monthlyOption.checked) {
            if (amountLabel) amountLabel.textContent = 'Preferred Monthly Amount (MK)';
            if (amountHint) amountHint.textContent = 'How much would you like to receive per month?';
        }
    }

    // Show/hide daily rate field and show modal if daily is selected and setup not complete
    if (dailyRateField) {
        const showDailyRate = dailyOption && dailyOption.checked;
        dailyRateField.classList.toggle('hidden', !showDailyRate);

        if (showDailyRate) {
            calculateDailyRequirements();

            // Show modal if setup is not complete
            if (!dailyRateConfig.dailyPaymentSetupComplete) {
                // Small delay to let the UI update first
                setTimeout(() => {
                    openDailyPaymentModal();
                }, 100);
            }
        }
    }

    // Update visual selection
    document.querySelectorAll('input[name="payment_preference"]').forEach(radio => {
        const label = radio.closest('label');
        if (radio.checked) {
            label.classList.add('border-indigo-500', 'bg-indigo-50');
            label.classList.remove('border-gray-300');
        } else {
            label.classList.remove('border-indigo-500', 'bg-indigo-50');
            label.classList.add('border-gray-300');
        }
    });
}

function calculateDailyRequirements() {
    const dailyRateInput = document.getElementById('daily_rate');
    const dailyRateInfo = document.getElementById('daily-rate-info');

    if (!dailyRateInput) return;

    const dailyRate = parseFloat(dailyRateInput.value) || 0;

    if (dailyRate <= 0) {
        if (dailyRateInfo) dailyRateInfo.classList.add('hidden');
        return;
    }

    // Show the info box
    if (dailyRateInfo) dailyRateInfo.classList.remove('hidden');

    // Calculate values using the config (which gets updated when modal saves)
    const totalBudget = dailyRateConfig.totalBudget;
    const totalTopics = dailyRateConfig.totalTopics;
    const maxDays = Math.floor(totalBudget / dailyRate);
    const topicsPerDay = maxDays > 0 ? Math.ceil(totalTopics / maxDays) : 0;

    // Update display
    const formatMoney = (amount) => 'MK ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('calc-total-budget').textContent = formatMoney(totalBudget);
    document.getElementById('calc-daily-rate').textContent = formatMoney(dailyRate);
    document.getElementById('calc-max-days').textContent = maxDays + ' days';
    document.getElementById('calc-total-topics').textContent = totalTopics + ' topics';
    document.getElementById('calc-topics-per-day').textContent = topicsPerDay;
    document.getElementById('calc-topics-per-day-text').textContent = topicsPerDay;
    document.getElementById('calc-daily-rate-text').textContent = formatMoney(dailyRate);

    // Update the config with current daily rate
    dailyRateConfig.currentDailyRate = dailyRate;
}

function togglePaymentMethodFields() {
    const bankOption = document.querySelector('input[name="payment_method"][value="bank"]');
    const bankFields = document.getElementById('bank-fields');
    const mobileFields = document.getElementById('mobile-fields');

    if (bankOption && bankFields && mobileFields) {
        bankFields.classList.toggle('hidden', !bankOption.checked);
        mobileFields.classList.toggle('hidden', bankOption.checked);
    }

    // Update visual selection
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        const label = radio.closest('label');
        if (radio.checked) {
            label.classList.add('border-indigo-500', 'bg-indigo-50');
            label.classList.remove('border-gray-300');
        } else {
            label.classList.remove('border-indigo-500', 'bg-indigo-50');
            label.classList.add('border-gray-300');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentPreference();
    togglePaymentMethodFields();

    // Add input event listener for real-time calculation
    const dailyRateInput = document.getElementById('daily_rate');
    if (dailyRateInput) {
        dailyRateInput.addEventListener('input', calculateDailyRequirements);
    }
});
</script>
@endsection
