<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f0e4">
    <title>@yield('title', 'Masuk') · {{ config('app.name', 'KeranjangKu') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/keranjangku-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/keranjangku-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-cream text-forest antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -left-32 -top-40 size-96 rounded-full bg-pastel-green/45 blur-3xl"></div>
        <div class="absolute -bottom-48 -right-32 size-[32rem] rounded-full bg-pastel-peach/60 blur-3xl"></div>
        <div class="absolute right-1/4 top-1/3 size-64 rounded-full bg-pastel-green-light/75 blur-3xl"></div>
    </div>
    <main class="relative flex min-h-screen items-center justify-center px-4 py-6 sm:px-8 sm:py-10">
        @yield('content')
    </main>
    @livewireScripts
    @fluxScripts
</body>
</html>
