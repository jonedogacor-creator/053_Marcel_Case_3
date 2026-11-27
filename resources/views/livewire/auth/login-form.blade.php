<form wire:submit="authenticate" class="mt-8 space-y-5">
    <flux:input id="email" wire:model="email" type="email" label="Email" required autofocus autocomplete="username" class:input="!rounded-xl !border-forest/15 !bg-cream !text-forest placeholder:!text-forest/40 focus:!border-pastel-green-strong" />
    <flux:input id="password" wire:model="password" type="password" label="Kata sandi" required autocomplete="current-password" viewable class:input="!rounded-xl !border-forest/15 !bg-cream !text-forest placeholder:!text-forest/40 focus:!border-pastel-green-strong" />
    <label class="flex items-center gap-2 text-sm font-medium text-forest/70">
        <input type="checkbox" wire:model="remember" class="size-4 rounded border-forest/20 accent-pastel-green-strong focus:ring-pastel-green-strong">
        Ingat saya
    </label>
    <flux:button type="submit" variant="primary" class="w-full !rounded-xl !border-pastel-green !bg-pastel-green !font-bold !text-forest shadow-lg shadow-emerald-950/10 transition hover:!border-pastel-green-strong hover:!bg-pastel-green-strong" wire:loading.attr="disabled" wire:target="authenticate">
        <span class="inline-flex items-center gap-2">
            <span wire:loading.remove wire:target="authenticate">Masuk</span>
            <span wire:loading wire:target="authenticate">Memeriksa...</span>
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a1 1 0 0 1 1-1h9.59L10.3 5.7a1 1 0 1 1 1.4-1.4l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 0 1-1.4-1.4l3.29-3.3H4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/></svg>
        </span>
    </flux:button>
</form>
