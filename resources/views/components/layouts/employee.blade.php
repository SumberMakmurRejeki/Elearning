<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-cloud text-ink">
        <div class="flex min-h-screen">
            <x-sidebar.employee />

            <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
                <x-topbar.employee />

                <main class="flex-1 p-4 md:p-6 xl:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
