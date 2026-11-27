<button
    type="button"
    wire:click="logout"
    wire:loading.attr="disabled"
    wire:target="logout"
    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-pastel-green px-3 py-2 text-sm font-bold text-forest shadow-sm transition hover:bg-pastel-green-strong focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest disabled:cursor-wait disabled:opacity-70 sm:px-4"
>
    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v3a1 1 0 1 1-2 0V4H5v12h6v-3a1 1 0 1 1 2 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4Zm11.293 2.293a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1 0 1.414l-3 3a1 1 0 0 1-1.414-1.414L15.586 11H8a1 1 0 1 1 0-2h7.586l-1.293-1.293a1 1 0 0 1 0-1.414Z" clip-rule="evenodd"/></svg>
    <span wire:loading.remove wire:target="logout">Keluar</span>
    <span wire:loading wire:target="logout">Keluar...</span>
</button>
