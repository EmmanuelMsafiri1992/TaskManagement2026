<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-indigo-50 via-white to-purple-50 relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-indigo-200 to-purple-200 opacity-20 rounded-full blur-3xl -mr-48 -mt-48"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-pink-200 to-indigo-200 opacity-20 rounded-full blur-3xl -ml-48 -mb-48"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <a href="/" class="inline-block">
                    @if(option('header_logo'))
                        <img src="{{ asset(option('header_logo')) }}" alt="{{ option('app_name', config('app.name', 'TaskHub')) }}" class="h-16 w-auto mx-auto mb-6">
                    @else
                        <div class="flex items-center justify-center space-x-3 mb-6">
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <x-application-logo class="w-10 h-10 fill-current text-white" />
                            </div>
                        </div>
                    @endif
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h1>
                <p class="text-gray-600">Sign in to {{ option('app_name', config('app.name', 'TaskHub')) }}</p>
            </div>

            <!-- Login Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10 backdrop-blur-sm bg-opacity-80">
                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email address')" class="text-sm font-semibold text-gray-700 mb-2" />
                        <x-text-input id="email"
                            class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-20 transition duration-200"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            placeholder="you@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-gray-700 mb-2" />
                        <x-text-input id="password"
                            class="block w-full px-4 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-20 transition duration-200"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me"
                                type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2 transition duration-200"
                                name="remember">
                            <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-900 transition duration-200">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition duration-200"
                               href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform transition duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl">
                            {{ __('Sign in') }}
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                @if (Route::has('register'))
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-200">
                                Request access
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600 mb-2">
                    Powered by <span class="font-semibold text-gray-900">Emphx Innovative Solutions</span>
                </p>
                <p class="text-xs text-gray-500">
                    &copy; {{ date('Y') }} {{ option('app_name', config('app.name', 'TaskHub')) }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
