<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Service Provider Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Impersonation Banner -->
    @if(session('impersonating_service_provider'))
    <div class="bg-amber-500 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="font-medium">
                        You are viewing as: <strong>{{ Auth::guard('service_provider')->user()->name }}</strong>
                        @if(session('impersonator_admin_name'))
                            (Logged in as {{ session('impersonator_admin_name') }})
                        @endif
                    </span>
                </div>
                <button onclick="stopImpersonating()" class="inline-flex items-center px-3 py-1 bg-white text-amber-600 rounded-md text-sm font-medium hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                    </svg>
                    Return to Admin
                </button>
            </div>
        </div>
    </div>
    <script>
        async function stopImpersonating() {
            try {
                const response = await fetch('/api/service-providers/stop-impersonating', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = data.redirect_url;
                } else {
                    alert('Failed to stop impersonating: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to stop impersonating');
            }
        }
    </script>
    @endif

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('service-provider.dashboard') }}" class="text-xl font-bold text-indigo-600">
                            Service Provider Portal
                        </a>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="{{ route('service-provider.dashboard') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('service-provider.dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('service-provider.recordings') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('service-provider.recordings*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            Recording Sessions
                        </a>
                        <a href="{{ route('service-provider.lesson-plans') }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('service-provider.lesson-plans*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            Lesson Plans
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ Auth::guard('service_provider')->user()->name }}</span>
                        <a href="{{ route('service-provider.profile') }}" class="text-sm text-gray-500 hover:text-gray-700">Profile</a>
                        <form method="POST" action="{{ route('service-provider.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
