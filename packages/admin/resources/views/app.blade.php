<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ option('app_direction', 'ltr') }}" class="bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="/favicon.png" type="image/x-icon">
    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <title>{{ config('app.name', 'Spack') }}</title>

    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <script>
        window.AppData = @json(new Admin\Support\AppData)
    </script>

    {{ vite_tags('vendor/admin', 5173, 'main.ts') }}
</head>
<body class="bg-gray-100">
    <div id="app"></div>
</body>
</html>
