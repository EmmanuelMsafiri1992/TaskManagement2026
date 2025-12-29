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
                            <p class="text-sm text-indigo-600">Your total agreed contract value</p>
                        </div>
                        <div class="text-2xl font-bold text-indigo-600">MK {{ number_format($provider->total_agreed_amount ?? 700000, 2) }}</div>
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
                        <p class="text-sm text-amber-700 mb-3">
                            Based on your daily rate, here's what you need to complete to ensure full payment:
                        </p>

                        @php
                            $totalTopics = $provider->getTotalTopicsCount() ?: 100; // Default estimate if no topics assigned
                            $amountPerSubject = $provider->amount_per_subject ?? 350000;
                            $subjectCount = $provider->assigned_subjects_count ?? 2;
                            $dailyRate = $provider->daily_rate ?? 40000;
                            $maxDays = $dailyRate > 0 ? floor(($amountPerSubject * $subjectCount) / $dailyRate) : 0;
                            $topicsPerDay = $maxDays > 0 ? ceil($totalTopics / $maxDays) : 0;
                        @endphp

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-white rounded p-3">
                                <span class="text-gray-600 block">Total Budget</span>
                                <span class="font-bold text-lg text-gray-900" id="calc-total-budget">MK {{ number_format($amountPerSubject * $subjectCount, 2) }}</span>
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
    totalBudget: {{ ($provider->amount_per_subject ?? 350000) * ($provider->assigned_subjects_count ?? 2) }},
    totalTopics: {{ $provider->getTotalTopicsCount() ?: 100 }},
    amountPerSubject: {{ $provider->amount_per_subject ?? 350000 }},
    subjectCount: {{ $provider->assigned_subjects_count ?? 2 }}
};

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

    // Show/hide daily rate field
    if (dailyRateField) {
        const showDailyRate = dailyOption && dailyOption.checked;
        dailyRateField.classList.toggle('hidden', !showDailyRate);

        if (showDailyRate) {
            calculateDailyRequirements();
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

    // Calculate values
    const totalBudget = dailyRateConfig.totalBudget;
    const totalTopics = dailyRateConfig.totalTopics;
    const maxDays = Math.floor(totalBudget / dailyRate);
    const topicsPerDay = maxDays > 0 ? Math.ceil(totalTopics / maxDays) : 0;

    // Update display
    const formatMoney = (amount) => 'MK ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('calc-daily-rate').textContent = formatMoney(dailyRate);
    document.getElementById('calc-max-days').textContent = maxDays + ' days';
    document.getElementById('calc-topics-per-day').textContent = topicsPerDay;
    document.getElementById('calc-topics-per-day-text').textContent = topicsPerDay;
    document.getElementById('calc-daily-rate-text').textContent = formatMoney(dailyRate);
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
