<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#f7f7f7] text-[#1a1a1a]">
    <div class="flex min-h-screen">
        <x-sidebar.admin />

        <div class="flex min-h-screen flex-1 flex-col lg:pl-[260px]">
            <x-topbar.admin />

            <main class="flex-1 p-6 md:p-8 xl:p-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
