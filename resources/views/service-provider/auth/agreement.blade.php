<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Agreement - Service Provider Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .agreement-content h1 { font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem; }
        .agreement-content h2 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .agreement-content p { margin-bottom: 0.75rem; }
        .agreement-content ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 0.75rem; }
        .agreement-content li { margin-bottom: 0.25rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">Service Provider Agreement</h1>
            <p class="mt-2 text-gray-600">Please read and sign the agreement to continue</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Agreement Content -->
            <div class="p-8 max-h-96 overflow-y-auto border-b agreement-content">
                {!! $agreementContent !!}
            </div>

            <!-- Signature Form -->
            <form method="POST" action="{{ route('service-provider.agreement') }}" class="p-8">
                @csrf

                <div class="mb-6">
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                        Digital Signature (Type your full name)
                    </label>
                    <input type="text" name="signature" id="signature" required
                           placeholder="Type your full name as signature"
                           class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                           style="font-family: 'Brush Script MT', cursive;">
                </div>

                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="agree" id="agree" required
                               class="mt-1 h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            I have read and understood the above agreement. I agree to all terms and conditions stated herein.
                            I understand that all content I create belongs to the company and I will be compensated for my services.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <form method="POST" action="{{ route('service-provider.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                            Cancel and Logout
                        </button>
                    </form>

                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign Agreement
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-gray-500">
            Signing as: <strong>{{ $provider->name }}</strong> ({{ $provider->email }})
        </p>
    </div>
</body>
</html>
