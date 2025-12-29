@extends('service-provider.layouts.auth')

@section('title', 'Register - Service Provider Portal')
@section('subtitle', 'Create your account')

@section('content')
<form method="POST" action="{{ route('service-provider.register') }}" class="space-y-6" id="registerForm">
    @csrf

    <!-- Step Indicator -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center cursor-pointer" onclick="showStep(1)">
                <span id="step1-indicator" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-indigo-600 text-white">1</span>
                <span class="ml-2 text-sm font-medium text-indigo-600">Personal Info</span>
            </div>
            <div class="flex-1 h-1 mx-4 bg-gray-200">
                <div id="progress-bar" class="h-1 bg-indigo-600 transition-all" style="width: 0%"></div>
            </div>
            <div class="flex items-center cursor-pointer" onclick="showStep(2)">
                <span id="step2-indicator" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-gray-200 text-gray-600">2</span>
                <span class="ml-2 text-sm font-medium text-gray-400">Payment Details</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Personal Information -->
    <div id="step1" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email address *</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="national_id" class="block text-sm font-medium text-gray-700">National ID *</label>
                <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('national_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}"
                   placeholder="Your residential address"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="specialty" class="block text-sm font-medium text-gray-700">Subject Specialty *</label>
            <input type="text" name="specialty" id="specialty" value="{{ old('specialty') }}" required
                   placeholder="e.g., Mathematics, Physics, English"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('specialty')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="qualification" class="block text-sm font-medium text-gray-700">Highest Qualification *</label>
            <input type="text" name="qualification" id="qualification" value="{{ old('qualification') }}" required
                   placeholder="e.g., Bachelor's in Education"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('qualification')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <button type="button" onclick="showStep(2)"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Next: Payment Details
        </button>
    </div>

    <!-- Step 2: Payment Information -->
    <div id="step2" class="space-y-4 hidden">
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4">
            <h3 class="font-medium text-indigo-800 mb-1">Payment Information</h3>
            <p class="text-sm text-indigo-600">Your total contract amount is MK 700,000. Choose how you'd like to be paid.</p>
        </div>

        <!-- Payment Preference -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Preference *</label>
            <div class="grid grid-cols-1 gap-3">
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-option" id="bi_weekly-option">
                    <input type="radio" name="payment_preference" value="bi_weekly" class="sr-only" {{ old('payment_preference', 'bi_weekly') == 'bi_weekly' ? 'checked' : '' }} onchange="togglePaymentPreference()">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center radio-indicator">
                                <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden check-mark"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">Bi-Weekly (Every 2 Weeks)</div>
                            <div class="text-sm text-gray-500">Receive payments every two weeks during your contract period</div>
                        </div>
                    </div>
                </label>
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-option" id="monthly-option">
                    <input type="radio" name="payment_preference" value="monthly" class="sr-only" {{ old('payment_preference') == 'monthly' ? 'checked' : '' }} onchange="togglePaymentPreference()">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center radio-indicator">
                                <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden check-mark"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">Monthly Payments</div>
                            <div class="text-sm text-gray-500">Receive payments each month during your contract period</div>
                        </div>
                    </div>
                </label>
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-option" id="daily-option">
                    <input type="radio" name="payment_preference" value="daily" class="sr-only" {{ old('payment_preference') == 'daily' ? 'checked' : '' }} onchange="togglePaymentPreference()">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center radio-indicator">
                                <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden check-mark"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">Daily (Per Day Rate)</div>
                            <div class="text-sm text-gray-500">Get paid per day based on topics completed</div>
                        </div>
                    </div>
                </label>
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-option" id="lump_sum-option">
                    <input type="radio" name="payment_preference" value="lump_sum" class="sr-only" {{ old('payment_preference') == 'lump_sum' ? 'checked' : '' }} onchange="togglePaymentPreference()">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center radio-indicator">
                                <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full hidden check-mark"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">Lump Sum (After Completion)</div>
                            <div class="text-sm text-gray-500">Receive full payment after completing all recordings</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Monthly Amount (shown for bi_weekly and monthly preference) -->
        <div id="monthly-amount-section">
            <label for="monthly_amount" class="block text-sm font-medium text-gray-700">Preferred Monthly Amount (MK)</label>
            <input type="number" name="monthly_amount" id="monthly_amount" value="{{ old('monthly_amount') }}"
                   placeholder="e.g., 100000"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-xs text-gray-500">How much would you like to receive per month from your MK 700,000 contract?</p>
        </div>

        <!-- Daily Rate Section (shown only for daily preference) -->
        <div id="daily-rate-section" class="hidden">
            <label for="daily_rate" class="block text-sm font-medium text-gray-700">Daily Rate (MK) *</label>
            <input type="number" name="daily_rate" id="daily_rate" value="{{ old('daily_rate') }}"
                   placeholder="e.g., 40000"
                   min="1000"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   onchange="calculateDailyRequirements()" oninput="calculateDailyRequirements()">
            <p class="mt-1 text-xs text-gray-500">How much do you want to be paid per day?</p>
            @error('daily_rate')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Daily Rate Calculator -->
            <div id="daily-rate-calculator" class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 hidden">
                <h4 class="font-medium text-amber-800 mb-2">Daily Payment Requirements</h4>
                <p class="text-sm text-amber-700 mb-3">Based on your chosen daily rate:</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-white rounded p-2">
                        <span class="text-gray-600 block text-xs">Total Budget</span>
                        <span class="font-bold text-gray-900">MK 700,000</span>
                    </div>
                    <div class="bg-white rounded p-2">
                        <span class="text-gray-600 block text-xs">Your Daily Rate</span>
                        <span class="font-bold text-gray-900" id="calc-daily-rate-display">MK 0</span>
                    </div>
                    <div class="bg-white rounded p-2">
                        <span class="text-gray-600 block text-xs">Max Payable Days</span>
                        <span class="font-bold text-indigo-600" id="calc-max-days-display">0 days</span>
                    </div>
                    <div class="bg-white rounded p-2">
                        <span class="text-gray-600 block text-xs">Est. Topics (100 total)</span>
                        <span class="font-bold text-indigo-600" id="calc-topics-per-day-display">0 per day</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-amber-600">
                    <strong>Note:</strong> You must complete the required number of topics per day to receive your daily payment.
                </p>
            </div>
        </div>

        <!-- Payment Method -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method *</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-method-option" id="bank-option">
                    <input type="radio" name="payment_method" value="bank" class="sr-only" {{ old('payment_method', 'bank') == 'bank' ? 'checked' : '' }} onchange="togglePaymentMethod()">
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <div class="font-medium text-gray-900">Bank Transfer</div>
                        <div class="text-xs text-gray-500">Direct deposit</div>
                    </div>
                </label>
                <label class="relative border rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition-all payment-method-option" id="mobile_money-option">
                    <input type="radio" name="payment_method" value="mobile_money" class="sr-only" {{ old('payment_method') == 'mobile_money' ? 'checked' : '' }} onchange="togglePaymentMethod()">
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="font-medium text-gray-900">Mobile Money</div>
                        <div class="text-xs text-gray-500">Airtel / TNM</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Bank Details -->
        <div id="bank-details" class="bg-gray-50 rounded-lg p-4 space-y-4">
            <h4 class="font-medium text-gray-900">Bank Account Details</h4>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                    <select name="bank_name" id="bank_name" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Bank</option>
                        <option value="National Bank of Malawi" {{ old('bank_name') == 'National Bank of Malawi' ? 'selected' : '' }}>National Bank of Malawi</option>
                        <option value="Standard Bank" {{ old('bank_name') == 'Standard Bank' ? 'selected' : '' }}>Standard Bank</option>
                        <option value="FDH Bank" {{ old('bank_name') == 'FDH Bank' ? 'selected' : '' }}>FDH Bank</option>
                        <option value="NBS Bank" {{ old('bank_name') == 'NBS Bank' ? 'selected' : '' }}>NBS Bank</option>
                        <option value="Ecobank" {{ old('bank_name') == 'Ecobank' ? 'selected' : '' }}>Ecobank</option>
                        <option value="First Capital Bank" {{ old('bank_name') == 'First Capital Bank' ? 'selected' : '' }}>First Capital Bank</option>
                        <option value="CDH Bank" {{ old('bank_name') == 'CDH Bank' ? 'selected' : '' }}>CDH Bank</option>
                        <option value="MyBucks" {{ old('bank_name') == 'MyBucks' ? 'selected' : '' }}>MyBucks</option>
                    </select>
                </div>
                <div>
                    <label for="bank_branch" class="block text-sm font-medium text-gray-700">Branch</label>
                    <input type="text" name="bank_branch" id="bank_branch" value="{{ old('bank_branch') }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="bank_account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                    <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name') }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="bank_account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number') }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Mobile Money Details -->
        <div id="mobile-money-details" class="bg-gray-50 rounded-lg p-4 space-y-4 hidden">
            <h4 class="font-medium text-gray-900">Mobile Money Details</h4>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="mobile_money_provider" class="block text-sm font-medium text-gray-700">Provider</label>
                    <select name="mobile_money_provider" id="mobile_money_provider" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Provider</option>
                        <option value="Airtel Money" {{ old('mobile_money_provider') == 'Airtel Money' ? 'selected' : '' }}>Airtel Money</option>
                        <option value="TNM Mpamba" {{ old('mobile_money_provider') == 'TNM Mpamba' ? 'selected' : '' }}>TNM Mpamba</option>
                    </select>
                </div>
                <div>
                    <label for="mobile_money_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="mobile_money_number" id="mobile_money_number" value="{{ old('mobile_money_number') }}"
                           placeholder="e.g., 0999123456"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="mobile_money_name" class="block text-sm font-medium text-gray-700">Registered Name</label>
                    <input type="text" name="mobile_money_name" id="mobile_money_name" value="{{ old('mobile_money_name') }}"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex space-x-3">
            <button type="button" onclick="showStep(1)"
                    class="flex-1 py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back
            </button>
            <button type="submit"
                    class="flex-1 py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Register
            </button>
        </div>
    </div>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Sign in
        </a>
    </p>
</div>

<script>
let currentStep = 1;

function showStep(step) {
    currentStep = step;

    document.getElementById('step1').classList.toggle('hidden', step !== 1);
    document.getElementById('step2').classList.toggle('hidden', step !== 2);

    // Update step indicators
    document.getElementById('step1-indicator').className = step >= 1
        ? 'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-indigo-600 text-white'
        : 'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-gray-200 text-gray-600';
    document.getElementById('step2-indicator').className = step >= 2
        ? 'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-indigo-600 text-white'
        : 'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium bg-gray-200 text-gray-600';

    document.getElementById('progress-bar').style.width = step >= 2 ? '100%' : '0%';
}

function togglePaymentPreference() {
    const biWeeklyOption = document.querySelector('input[name="payment_preference"][value="bi_weekly"]');
    const monthlyOption = document.querySelector('input[name="payment_preference"][value="monthly"]');
    const dailyOption = document.querySelector('input[name="payment_preference"][value="daily"]');
    const amountSection = document.getElementById('monthly-amount-section');
    const dailyRateSection = document.getElementById('daily-rate-section');
    const amountLabel = document.querySelector('#monthly-amount-section label');
    const amountHint = document.querySelector('#monthly-amount-section .text-xs');

    // Show amount section for bi_weekly and monthly
    const showAmount = biWeeklyOption.checked || monthlyOption.checked;
    amountSection.classList.toggle('hidden', !showAmount);

    // Show daily rate section for daily preference
    const showDailyRate = dailyOption && dailyOption.checked;
    dailyRateSection.classList.toggle('hidden', !showDailyRate);

    // Update label based on selection
    if (biWeeklyOption.checked) {
        amountLabel.textContent = 'Preferred Bi-Weekly Amount (MK)';
        amountHint.textContent = 'How much would you like to receive every 2 weeks from your contract?';
    } else if (monthlyOption.checked) {
        amountLabel.textContent = 'Preferred Monthly Amount (MK)';
        amountHint.textContent = 'How much would you like to receive per month from your contract?';
    }

    // Calculate daily requirements if daily is selected
    if (showDailyRate) {
        calculateDailyRequirements();
    }

    // Update visual selection
    document.querySelectorAll('.payment-option').forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        const indicator = option.querySelector('.radio-indicator');
        const checkMark = option.querySelector('.check-mark');

        if (radio.checked) {
            option.classList.add('border-indigo-500', 'bg-indigo-50');
            indicator.classList.add('border-indigo-600');
            checkMark.classList.remove('hidden');
        } else {
            option.classList.remove('border-indigo-500', 'bg-indigo-50');
            indicator.classList.remove('border-indigo-600');
            checkMark.classList.add('hidden');
        }
    });
}

function calculateDailyRequirements() {
    const dailyRateInput = document.getElementById('daily_rate');
    const calculator = document.getElementById('daily-rate-calculator');

    if (!dailyRateInput) return;

    const dailyRate = parseFloat(dailyRateInput.value) || 0;

    if (dailyRate <= 0) {
        if (calculator) calculator.classList.add('hidden');
        return;
    }

    // Show calculator
    if (calculator) calculator.classList.remove('hidden');

    // Calculate values (700,000 total budget, estimated 100 topics)
    const totalBudget = 700000;
    const estimatedTopics = 100;
    const maxDays = Math.floor(totalBudget / dailyRate);
    const topicsPerDay = maxDays > 0 ? Math.ceil(estimatedTopics / maxDays) : 0;

    // Format money
    const formatMoney = (amount) => 'MK ' + amount.toLocaleString('en-US');

    // Update display
    document.getElementById('calc-daily-rate-display').textContent = formatMoney(dailyRate);
    document.getElementById('calc-max-days-display').textContent = maxDays + ' days';
    document.getElementById('calc-topics-per-day-display').textContent = topicsPerDay + ' per day';
}

function togglePaymentMethod() {
    const bankOption = document.querySelector('input[name="payment_method"][value="bank"]');
    const bankDetails = document.getElementById('bank-details');
    const mobileDetails = document.getElementById('mobile-money-details');

    bankDetails.classList.toggle('hidden', !bankOption.checked);
    mobileDetails.classList.toggle('hidden', bankOption.checked);

    // Update visual selection
    document.querySelectorAll('.payment-method-option').forEach(option => {
        const radio = option.querySelector('input[type="radio"]');

        if (radio.checked) {
            option.classList.add('border-indigo-500', 'bg-indigo-50');
        } else {
            option.classList.remove('border-indigo-500', 'bg-indigo-50');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentPreference();
    togglePaymentMethod();

    // Check for validation errors and show appropriate step
    @if($errors->any())
        @if($errors->has('payment_preference') || $errors->has('payment_method') || $errors->has('bank_name') || $errors->has('mobile_money_provider'))
            showStep(2);
        @endif
    @endif
});
</script>
@endsection
