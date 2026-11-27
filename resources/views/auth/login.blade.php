@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <section class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-forest/10 bg-white shadow-2xl shadow-emerald-950/15 ring-1 ring-white/70 lg:min-h-[min(48rem,calc(100vh-3rem))] lg:grid-cols-[1.05fr_0.95fr]" aria-labelledby="login-title">
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-linear-to-br from-forest via-[#285640] to-[#52885f] p-8 text-white lg:flex lg:p-12 xl:p-14">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full border border-white/15" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-36 -left-20 size-96 rounded-full border border-white/10" aria-hidden="true"></div>
            <div class="pointer-events-none absolute right-8 top-1/4 size-48 rounded-full bg-pastel-peach/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute bottom-1/4 left-1/3 size-48 rounded-full bg-pastel-green/20 blur-3xl" aria-hidden="true"></div>

            <a href="{{ route('login') }}" class="relative inline-flex items-center gap-3">
                <x-brand-mark size="size-14" class="shadow-black/20" />
                <span>
                    <span class="block text-xl font-black tracking-tight">KeranjangKu</span>
                    <span class="block text-xs font-medium text-white/65">YOUR STOCK, YOUR FLOW</span>
                </span>
            </a>

            <div class="relative max-w-lg">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-bold tracking-[0.16em] text-pastel-green-light">
                    <span class="size-2 rounded-full bg-pastel-green" aria-hidden="true"></span>
                    INVENTORY, BUT MAKE IT EASY
                </span>
                <h2 class="mt-6 text-5xl font-black leading-[0.98] tracking-tight xl:text-6xl">
                    Stok rapi,<br>
                    <span class="text-pastel-green">vibes happy.</span>
                </h2>
                <p class="mt-6 max-w-md text-base leading-7 text-white/70">
                    Semua produk, jumlah stok, dan nilainya dalam satu workspace yang gampang dipantau.
                </p>

                <div class="mt-9 grid max-w-md grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <span class="grid size-9 place-items-center rounded-xl bg-pastel-green text-forest" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><path d="m10 2 7 3.5v9L10 18l-7-3.5v-9L10 2Zm0 2.2L5.5 6.5 10 8.8l4.5-2.3L10 4.2Zm-5 4v5.1l4 2v-5.1l-4-2Zm6 7.1 4-2V8.2l-4 2v5.1Z"/></svg>
                        </span>
                        <span class="mt-4 block text-sm font-bold">Catat produk</span>
                        <span class="mt-1 block text-xs text-white/55">Semua tersimpan rapi</span>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <span class="grid size-9 place-items-center rounded-xl bg-pastel-peach text-forest" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 1 0 8 8 8 8 0 0 0-8-8Zm1 3a1 1 0 1 0-2 0v5a1 1 0 0 0 .45.83l3 2a1 1 0 1 0 1.1-1.66L11 9.46V5Z" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="mt-4 block text-sm font-bold">Pantau stok</span>
                        <span class="mt-1 block text-xs text-white/55">Update tanpa ribet</span>
                    </div>
                </div>
            </div>

            <p class="relative text-sm font-medium text-white/55">Sederhana untuk dimulai. Mudah untuk berkembang.</p>
        </aside>

        <div class="flex items-center justify-center bg-white px-6 py-8 sm:px-10 sm:py-12 lg:px-12">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-9 inline-flex items-center gap-3 lg:hidden">
                    <x-brand-mark />
                    <span>
                        <span class="block text-lg font-black tracking-tight text-forest">KeranjangKu</span>
                        <span class="block text-[10px] font-bold tracking-[0.14em] text-emerald-800">YOUR STOCK, YOUR FLOW</span>
                    </span>
                </a>

                <span class="inline-flex items-center gap-2 rounded-full bg-pastel-green-light px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-forest">
                    <span class="size-1.5 rounded-full bg-forest" aria-hidden="true"></span>
                    Selamat datang kembali
                </span>
                <h1 id="login-title" class="mt-5 text-3xl font-black tracking-tight text-forest sm:text-4xl">Masuk ke akun</h1>
                <p class="mt-3 text-sm leading-6 text-forest/65">Lanjutkan kelola produk dan pantau stokmu. Semua siap, tinggal masuk.</p>

                @if (session('status'))
                    <div role="status" class="mt-6 rounded-2xl border border-pastel-green-strong bg-pastel-green-light px-4 py-3 text-sm font-medium text-forest">{{ session('status') }}</div>
                @endif

                <livewire:auth.login-form />

                <p class="mt-8 text-center text-sm text-forest/65">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-bold text-forest underline decoration-pastel-green-strong underline-offset-4 transition hover:text-emerald-800">Daftar gratis</a>
                </p>
                <p class="mt-6 text-center text-xs text-forest/45">Data produkmu tersimpan aman dan hanya terlihat oleh akunmu.</p>
            </div>
        </div>
    </section>
@endsection
