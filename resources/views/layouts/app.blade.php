<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'FalaVizin') }}</title>

        @php $metaDescription = $description ?? 'Serviços, eventos, avisos e muito mais — tudo do seu bairro, em um só lugar.'; @endphp
        <meta name="description" content="{{ $metaDescription }}">
        <meta property="og:title" content="{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'FalaVizin') }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:type" content="website">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-stone-50 text-stone-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>
</html>
