<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f6f0e4">
    <title>@isset($title){{ $title }} · @endisset{{ config('app.name', 'KeranjangKu') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/keranjangku-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/keranjangku-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-cream text-forest antialiased">
    <div
        x-data="{
            menuOpen: false,
            infoOpen: false,
            toastOpen: false,
            toastMessage: '',
            toastTimer: null,
            showToast(event) {
                this.toastMessage = event.detail.message;
                this.toastOpen = true;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => this.toastOpen = false, 3500);
            }
        }"
        x-on:notify.window="showToast($event)"
        class="min-h-screen"
    >
        <div x-cloak x-show="toastOpen" x-transition role="status" aria-live="polite" class="fixed right-4 top-4 z-[60] flex max-w-sm items-start gap-3 rounded-2xl border border-forest/10 bg-white p-4 text-forest shadow-xl shadow-emerald-950/10 sm:right-6 sm:top-6">
            <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-full bg-pastel-green-light text-forest" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42.003L3.29 9.168a1 1 0 0 1 1.42-1.407l4.09 4.13 6.49-6.596a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd"/></svg>
            </span>
            <p class="flex-1 pt-1 text-sm font-medium text-forest" x-text="toastMessage"></p>
            <button type="button" @click="toastOpen = false" class="rounded-lg p-1 text-forest hover:bg-pastel-green-light" aria-label="Tutup notifikasi">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"/></svg>
            </button>
        </div>

        <header class="sticky top-0 z-40 border-b border-forest/10 bg-white/85 shadow-sm shadow-emerald-950/5 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-3 py-3 sm:gap-4 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2 sm:gap-3" aria-label="KeranjangKu, dashboard">
                    <x-brand-mark />
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold tracking-tight text-forest sm:text-lg">KeranjangKu</span>
                        <span class="hidden text-xs text-forest/60 sm:block">Ruang stok yang lebih tertata</span>
                    </span>
                </a>
                <nav class="hidden items-center gap-6 sm:flex" aria-label="Navigasi utama">
                    <a href="{{ route('dashboard') }}" class="text-sm font-bold text-forest underline decoration-pastel-green-strong underline-offset-4" aria-current="page">Dashboard</a>
                    <span class="h-5 w-px bg-forest/15" aria-hidden="true"></span>
                    <span class="max-w-40 truncate text-sm text-forest/70">{{ auth()->user()->name }}</span>
                </nav>
                <div class="flex shrink-0 items-center gap-2">
                    <div class="relative hidden sm:block">
                        <button type="button" @click="infoOpen = !infoOpen" :aria-expanded="infoOpen.toString()" aria-controls="system-info" class="inline-flex items-center gap-2 rounded-xl border border-forest/15 bg-white px-3 py-2 text-sm font-semibold text-forest transition hover:bg-pastel-green-light">
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9.25 8.5a.75.75 0 0 1 1.5 0v.5h.25a.75.75 0 0 1 0 1.5h-1v1.25a.75.75 0 0 1-1.5 0V10a1.5 1.5 0 0 1 .75-1.3V8.5ZM10 6.75a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z" clip-rule="evenodd"/></svg>
                            <span class="hidden sm:inline">Info sistem</span>
                        </button>
                        <div id="system-info" x-cloak x-show="infoOpen" x-transition @click.outside="infoOpen = false" class="absolute right-0 mt-3 w-72 rounded-2xl border border-forest/10 bg-white p-5 text-forest shadow-xl shadow-emerald-950/10">
                            <p class="text-sm font-semibold">Tentang KeranjangKu</p>
                            <p class="mt-2 text-sm leading-6 text-forest/70">Simpan produk, pantau jumlah stok, dan pulihkan barang yang dipindahkan ke sampah.</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-full bg-pastel-green-light px-2.5 py-1 text-xs font-bold text-forest">Livewire 4</span>
                                <span class="rounded-full bg-pastel-green-light px-2.5 py-1 text-xs font-bold text-forest">Flux UI 2</span>
                            </div>
                        </div>
                    </div>
                    <livewire:auth.logout-button />
                    <button type="button" class="rounded-xl p-2 text-forest hover:bg-pastel-green-light sm:hidden" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-label="Buka navigasi">
                        <svg x-show="!menuOpen" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-cloak x-show="menuOpen" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
            </div>
            <nav x-cloak x-show="menuOpen" x-transition class="border-t border-forest/10 bg-white px-4 py-4 sm:hidden" aria-label="Navigasi seluler">
                <p class="mb-3 truncate text-sm text-forest/60">{{ auth()->user()->name }}</p>
                <a href="{{ route('dashboard') }}" class="block rounded-xl bg-pastel-green-light px-3 py-2.5 text-sm font-bold text-forest">Dashboard produk</a>
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
            {{ $slot }}
        </main>
        <footer class="mx-auto max-w-7xl px-4 pb-8 text-center text-xs text-forest/50 sm:px-6 lg:px-8">
            KeranjangKu · Data produk dipisahkan untuk setiap akun
        </footer>
    </div>
    @livewireScripts
    @fluxScripts
</body>
</html>
