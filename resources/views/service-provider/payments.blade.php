@extends('service-provider.layouts.app')

@section('title', 'Payment History - Service Provider Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Payment History</h1>
    <p class="mt-1 text-gray-600">View all your payments and transaction records</p>
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
</div>

<!-- Payment Details Info -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Preferences</h2>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-gray-500">Payment Preference</dt>
                <dd class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $provider->payment_preference ?? 'Monthly')) }}</dd>
            </div>
            @if($provider->payment_preference === 'monthly' && $provider->monthly_amount)
            <div class="flex justify-between">
                <dt class="text-gray-500">Monthly Amount</dt>
                <dd class="font-medium text-gray-900">MK {{ number_format($provider->monthly_amount, 2) }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
        @if($provider->payment_method === 'bank')
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-medium text-gray-900">Bank Transfer</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Bank</dt>
                    <dd class="font-medium text-gray-900">{{ $provider->bank_name ?? 'Not set' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Account</dt>
                    <dd class="font-medium text-gray-900">{{ $provider->bank_account_number ? '****' . substr($provider->bank_account_number, -4) : 'Not set' }}</dd>
                </div>
            </dl>
        @elseif($provider->payment_method === 'mobile_money')
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-medium text-gray-900">Mobile Money</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Provider</dt>
                    <dd class="font-medium text-gray-900">{{ $provider->mobile_money_provider ?? 'Not set' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Number</dt>
                    <dd class="font-medium text-gray-900">{{ $provider->mobile_money_number ?? 'Not set' }}</dd>
                </div>
            </dl>
        @else
            <p class="text-gray-500">No payment method configured. <a href="{{ route('service-provider.profile') }}" class="text-indigo-600 hover:underline">Set up now</a></p>
        @endif
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Payment Records</h2>
    </div>

    @if($payments->count() > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment For</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $payment->receipt_number }}</div>
                            @if($payment->reference_number)
                                <div class="text-xs text-gray-500">Ref: {{ $payment->reference_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $payment->payment_date->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $payment->payment_date->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-green-600">MK {{ number_format($payment->amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $payment->month_for ?? 'Service Payment' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        @endif
    @else
        <div class="px-6 py-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No payments yet</h3>
            <p class="text-gray-500">Your payment records will appear here once payments are processed.</p>
        </div>
    @endif
</div>

<!-- Help Section -->
<div class="mt-8 bg-blue-50 rounded-lg shadow p-6">
    <div class="flex">
        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <h3 class="text-lg font-medium text-blue-800 mb-1">Payment Information</h3>
            <p class="text-sm text-blue-600">
                Payments are processed according to your selected preference. If you have any questions about your payments or need to update your payment details,
                please visit your <a href="{{ route('service-provider.profile') }}" class="underline font-medium">profile page</a> or contact the administrator.
            </p>
        </div>
    </div>
</div>
@endsection
