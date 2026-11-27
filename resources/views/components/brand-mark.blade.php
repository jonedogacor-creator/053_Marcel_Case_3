@props(['size' => 'size-11'])

<span {{ $attributes->class([$size, 'block shrink-0 overflow-hidden rounded-2xl bg-pastel-green shadow-lg shadow-ink/10 ring-1 ring-ink/10']) }} aria-hidden="true">
    <img src="{{ asset('images/keranjangku-icon.png') }}" alt="" class="h-full w-full object-cover">
</span>
