@extends('service-provider.layouts.auth')

@section('title', 'Login - Service Provider Portal')
@section('subtitle', 'Sign in to your account')

@section('content')
<form method="POST" action="{{ route('service-provider.login') }}" class="space-y-6">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
        </div>
    </div>

    <button type="submit"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Sign in
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('service-provider.register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Register here
        </a>
    </p>
</div>
@endsection
