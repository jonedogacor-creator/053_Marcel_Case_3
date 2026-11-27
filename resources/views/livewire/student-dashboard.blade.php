<div class="space-y-6">
    <section class="flex flex-col justify-between gap-5 rounded-3xl border border-amber-200 bg-white/80 p-6 shadow-sm backdrop-blur-sm sm:flex-row sm:items-end sm:p-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-amber-800">Portal mahasiswa UNSRI</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Lengkapi profil dan ceritakan minat serta bakatmu. Informasi ini membantu kampus mengenal potensimu.</p>
        </div>
        <div class="flex shrink-0 items-center gap-3 text-sm text-slate-600">
            <span class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-800" aria-hidden="true">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3.5" y="5" width="17" height="16" rx="2" stroke-width="1.7"/><path d="M7.5 3v4M16.5 3v4M3.5 10h17" stroke-width="1.7" stroke-linecap="round"/></svg>
            </span>
            <span>{{ $today }}</span>
        </div>
    </section>

    @if (session('status'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-3xl border border-amber-200 bg-white/95 p-5 shadow-sm sm:p-6" aria-labelledby="student-records-title">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-amber-800">Data dalam akun ini</p>
                <h2 id="student-records-title" class="mt-1 text-xl font-bold text-slate-900">Data mahasiswa ({{ $profiles->count() }})</h2>
                <p class="mt-1 text-sm text-slate-600">Satu akun dapat menyimpan data beberapa mahasiswa.</p>
            </div>
            <flux:button type="button" variant="primary" wire:click="startNewProfile">+ Tambah mahasiswa</flux:button>
        </div>

        @if ($profiles->isEmpty())
            <p class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Belum ada data mahasiswa. Isi formulir di bawah untuk menambahkan data pertama.</p>
        @else
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($profiles as $studentProfile)
                    <div wire:key="student-profile-{{ $studentProfile->id }}" class="flex min-w-0 overflow-hidden rounded-2xl border {{ $selectedProfileId === $studentProfile->id ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-100' : 'border-slate-200 bg-white' }}">
                        <button
                            type="button"
                            wire:click="selectProfile({{ $studentProfile->id }})"
                            aria-pressed="{{ $selectedProfileId === $studentProfile->id ? 'true' : 'false' }}"
                            class="min-w-0 flex-1 p-4 text-left transition hover:bg-amber-50"
                        >
                            <span class="block truncate font-semibold text-slate-900">{{ $studentProfile->student_name }}</span>
                            <span class="mt-1 block text-sm text-slate-600">NIM {{ $studentProfile->student_number }}</span>
                            <span class="mt-3 block text-xs font-semibold text-amber-800">{{ $selectedProfileId === $studentProfile->id ? 'Sedang dipilih' : 'Pilih untuk melihat atau mengubah' }}</span>
                        </button>
                        <div class="flex shrink-0 items-center border-l border-slate-200 p-2">
                            <flux:button
                                type="button"
                                size="sm"
                                variant="danger"
                                wire:click="deleteProfile({{ $studentProfile->id }})"
                                wire:confirm="Hapus data {{ $studentProfile->student_name }}? Tindakan ini tidak dapat dibatalkan."
                            >
                                Hapus
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan pendataan">
        <article class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800" aria-hidden="true">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path d="M12 7v5l3 2" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <span>
                <span class="block text-sm text-slate-500">Kelengkapan profil</span>
                <span class="mt-1 block text-2xl font-bold text-slate-900">{{ $completion }}%</span>
            </span>
        </article>
        <article class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3.5 14.2 9l5.8.4-4.4 3.8 1.4 5.7-5-3.1-5 3.1 1.4-5.7L4 9.4 9.8 9 12 3.5Z" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </span>
            <span>
                <span class="block text-sm text-slate-500">Bidang minat</span>
                <span class="mt-1 block text-2xl font-bold text-slate-900">{{ $interestCount }} <span class="text-sm font-medium text-slate-500">tercatat</span></span>
            </span>
        </article>
        <article class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700" aria-hidden="true">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3.5 14.7 8l5.2 1.1-3.6 3.9.5 5.4-4.8-2.2-4.8 2.2.5-5.4-3.6-3.9L9.3 8 12 3.5Z" stroke-width="1.7" stroke-linejoin="round"/><path d="m9.5 12 1.6 1.6 3.5-3.5" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span>
                <span class="block text-sm text-slate-500">Bakat dan kemampuan</span>
                <span class="mt-1 block text-2xl font-bold text-slate-900">{{ $talentCount }} <span class="text-sm font-medium text-slate-500">tercatat</span></span>
            </span>
        </article>
    </section>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.75fr)]">
        <section class="overflow-hidden rounded-3xl border border-amber-200 bg-white/95 shadow-sm" aria-labelledby="profile-form-title">
            <div class="border-b border-amber-100 px-5 py-5 sm:px-7">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-amber-800">{{ $selectedProfileId ? 'Data tersimpan' : 'Data baru' }}</p>
                        <h2 id="profile-form-title" class="mt-1 text-xl font-bold text-slate-900">{{ $selectedProfileId ? 'Ubah data mahasiswa' : 'Tambah data mahasiswa' }}</h2>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $completion === 100 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-100 text-amber-900' }}">
                        {{ $completion === 100 ? 'Profil lengkap' : 'Sedang dilengkapi' }}
                    </span>
                </div>
            </div>

            <form wire:submit="save" class="space-y-6 p-5 sm:p-7">
                <div class="flex flex-col gap-5 rounded-2xl bg-amber-50/80 p-4 sm:flex-row sm:items-center sm:p-5">
                    <div class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                        @if ($photo && in_array($photo->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true))
                            <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto profil" class="size-full object-cover">
                        @elseif ($profile?->photo_path)
                            <img src="{{ route('profile.photo', ['studentProfileId' => $profile->id]) }}" alt="Foto {{ $profile->student_name }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full flex-col items-center justify-center gap-1 text-amber-800">
                                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke-width="1.6"/><path d="M5 20c.7-3.4 3.1-5 7-5s6.3 1.6 7 5" stroke-width="1.6" stroke-linecap="round"/></svg>
                                <span class="text-[10px] font-semibold uppercase tracking-wide">Foto mahasiswa</span>
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-slate-900">Foto profil</h3>
                        <p class="mt-1 text-sm leading-5 text-slate-600">Gunakan foto terbaru dengan wajah terlihat jelas. Foto hanya dapat dilihat setelah masuk ke akun.</p>
                        <label for="student-photo" class="mt-3 inline-flex cursor-pointer items-center justify-center rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-100">
                            Pilih foto
                            <input id="student-photo" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        <span wire:loading wire:target="photo" class="ml-2 text-xs font-medium text-slate-500">Mengunggah foto…</span>
                        <p class="mt-2 text-xs text-slate-500">JPG, PNG, atau WEBP · Maksimal 2 MB</p>
                        @error('photo')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="student-name" class="mb-2 block text-sm font-medium text-slate-700">Nama mahasiswa <span class="text-red-600">*</span></label>
                        <input id="student-name" wire:model.blur="studentName" type="text" maxlength="150" autocomplete="name" required placeholder="Nama lengkap mahasiswa" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="student-name-error">
                        @error('studentName')<p id="student-name-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student-number" class="mb-2 block text-sm font-medium text-slate-700">Nomor Induk Mahasiswa (NIM) <span class="text-red-600">*</span></label>
                        <input id="student-number" wire:model.blur="studentNumber" type="text" maxlength="30" autocomplete="off" required placeholder="Contoh: 0903128242" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="student-number-error">
                        @error('studentNumber')<p id="student-number-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student-phone" class="mb-2 block text-sm font-medium text-slate-700">Nomor telepon</label>
                        <input id="student-phone" wire:model.blur="phone" type="tel" maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="phone-error">
                        @error('phone')<p id="phone-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student-faculty" class="mb-2 block text-sm font-medium text-slate-700">Fakultas <span class="text-red-600">*</span></label>
                        <input id="student-faculty" wire:model.blur="faculty" type="text" maxlength="150" required placeholder="Nama fakultas" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="faculty-error">
                        @error('faculty')<p id="faculty-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student-program" class="mb-2 block text-sm font-medium text-slate-700">Program studi <span class="text-red-600">*</span></label>
                        <input id="student-program" wire:model.blur="studyProgram" type="text" maxlength="150" required placeholder="Nama program studi" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="program-error">
                        @error('studyProgram')<p id="program-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="entry-year" class="mb-2 block text-sm font-medium text-slate-700">Tahun masuk <span class="text-red-600">*</span></label>
                        <input id="entry-year" wire:model.blur="entryYear" type="number" min="1950" max="{{ now()->year }}" required placeholder="{{ now()->year }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="entry-year-error">
                        @error('entryYear')<p id="entry-year-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="student-interests" class="mb-2 block text-sm font-medium text-slate-700">Minat <span class="text-red-600">*</span></label>
                        <textarea id="student-interests" wire:model.blur="interests" rows="3" maxlength="2000" required placeholder="Contoh: teknologi, olahraga, seni, kewirausahaan. Pisahkan dengan koma." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="interests-error"></textarea>
                        @error('interests')<p id="interests-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="student-talents" class="mb-2 block text-sm font-medium text-slate-700">Bakat dan kemampuan <span class="text-red-600">*</span></label>
                        <textarea id="student-talents" wire:model.blur="talents" rows="3" maxlength="2000" required placeholder="Ceritakan kemampuan, keahlian, atau prestasi yang kamu miliki." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="talents-error"></textarea>
                        @error('talents')<p id="talents-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="student-bio" class="mb-2 block text-sm font-medium text-slate-700">Tentang saya <span class="text-slate-400">(opsional)</span></label>
                        <textarea id="student-bio" wire:model.blur="bio" rows="3" maxlength="1000" placeholder="Tuliskan deskripsi singkat tentang dirimu." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100" aria-describedby="bio-error"></textarea>
                        @error('bio')<p id="bio-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-5 text-slate-500">Setiap data mahasiswa tersimpan di akunmu dan dapat diperbarui kapan saja.</p>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save,photo">
                        <span wire:loading.remove wire:target="save">{{ $selectedProfileId ? 'Simpan perubahan' : 'Simpan data mahasiswa' }}</span>
                        <span wire:loading wire:target="save">Menyimpan…</span>
                    </flux:button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-amber-200 bg-white/95 p-5 shadow-sm sm:p-6" aria-labelledby="completion-title">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Progress pendataan</p>
                        <h2 id="completion-title" class="mt-1 text-lg font-bold text-slate-900">Profil mahasiswa</h2>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $completion }}%</span>
                </div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-amber-100" role="progressbar" aria-label="Kelengkapan profil" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-amber-400 transition-all duration-500" style="width: {{ $completion }}%"></div>
                </div>
                <ul class="mt-5 space-y-3">
                    @foreach ($checklist as $item)
                        <li wire:key="checklist-{{ $loop->index }}" class="flex items-center gap-3 text-sm">
                            <span class="flex size-5 shrink-0 items-center justify-center rounded-full {{ $item['complete'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}" aria-hidden="true">
                                @if ($item['complete'])
                                    <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path d="m4 10 4 4 8-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @else
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                @endif
                            </span>
                            <span class="{{ $item['complete'] ? 'text-slate-700' : 'text-slate-500' }}">{{ $item['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="overflow-hidden rounded-3xl bg-slate-900 p-5 text-white shadow-sm sm:p-6" aria-labelledby="guide-title">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-amber-300">Panduan singkat</p>
                        <h2 id="guide-title" class="mt-1 text-lg font-bold">Ceritakan potensimu</h2>
                    </div>
                    <span class="flex size-11 items-center justify-center rounded-full bg-white/10 text-amber-300" aria-hidden="true">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3.5 14.3 9l5.7.5-4.4 3.7 1.4 5.8-5-3.2-5 3.2 1.4-5.8L4 9.5 9.7 9 12 3.5Z" stroke-width="1.6" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <div class="mt-5 space-y-4 text-sm leading-5 text-slate-300">
                    <p><span class="font-semibold text-white">Minat</span> adalah kegiatan atau bidang yang kamu sukai dan ingin pelajari lebih jauh.</p>
                    <p><span class="font-semibold text-white">Bakat</span> adalah kemampuan yang kamu kuasai atau ingin kembangkan, baik melalui organisasi, karya, maupun prestasi.</p>
                </div>
                <div class="mt-5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-xs leading-5 text-slate-300">
                    Pisahkan beberapa minat atau bakat dengan tanda koma agar mudah dibaca.
                </div>
            </section>

            <section class="rounded-3xl border border-amber-200 bg-amber-100/80 p-5 sm:p-6" aria-label="Privasi data">
                <div class="flex gap-3">
                    <svg class="mt-0.5 size-5 shrink-0 text-amber-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3 19 6v5c0 4.7-2.8 8-7 10-4.2-2-7-5.3-7-10V6l7-3Z" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div>
                        <h2 class="text-sm font-semibold text-amber-950">Data tetap privat</h2>
                        <p class="mt-1 text-xs leading-5 text-amber-950/75">Informasi dan foto profil hanya ditampilkan pada akun yang sedang masuk.</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
