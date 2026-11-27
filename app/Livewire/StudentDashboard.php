<?php

namespace App\Livewire;

use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('Dashboard Pendataan')]
class StudentDashboard extends Component
{
    use WithFileUploads;

    public ?int $selectedProfileId = null;

    public string $studentName = '';

    public string $studentNumber = '';

    public string $phone = '';

    public string $faculty = '';

    public string $studyProgram = '';

    public string $entryYear = '';

    public string $interests = '';

    public string $talents = '';

    public string $bio = '';

    public ?TemporaryUploadedFile $photo = null;

    public function mount(): void
    {
        $profile = Auth::user()->studentProfiles()->oldest('id')->first();

        if ($profile !== null) {
            $this->loadProfile($profile);
        }
    }

    public function startNewProfile(): void
    {
        $this->reset([
            'selectedProfileId',
            'studentName',
            'studentNumber',
            'phone',
            'faculty',
            'studyProgram',
            'entryYear',
            'interests',
            'talents',
            'bio',
            'photo',
        ]);
        $this->resetValidation();
    }

    public function selectProfile(int $profileId): void
    {
        $profile = Auth::user()->studentProfiles()->findOrFail($profileId);

        $this->loadProfile($profile);
        $this->resetValidation();
    }

    public function deleteProfile(int $profileId): void
    {
        $user = Auth::user();
        $profile = $user->studentProfiles()->findOrFail($profileId);
        $photoPath = $profile->photo_path;
        $wasSelected = $this->selectedProfileId === (int) $profile->getKey();

        if (! $profile->delete()) {
            throw new RuntimeException('Data mahasiswa gagal dihapus. Silakan coba kembali.');
        }

        if (filled($photoPath) && ! Storage::disk('local')->delete($photoPath)) {
            logger()->warning('Unable to delete the student profile photo.', [
                'user_id' => $user->getKey(),
                'student_profile_id' => $profile->getKey(),
                'path' => $photoPath,
            ]);
        }

        if ($wasSelected) {
            $nextProfile = $user->studentProfiles()->oldest('id')->first();

            if ($nextProfile !== null) {
                $this->loadProfile($nextProfile);
            } else {
                $this->startNewProfile();
            }
        }

        $this->resetValidation();
        session()->flash('status', 'Data mahasiswa berhasil dihapus.');
    }

    public function save(): void
    {
        $user = Auth::user();
        $profile = $this->selectedProfileId === null
            ? null
            : $user->studentProfiles()->findOrFail($this->selectedProfileId);
        $isNewProfile = $profile === null;
        $studentNumberRule = Rule::unique('student_profiles', 'student_number');

        if ($profile !== null) {
            $studentNumberRule->ignore($profile);
        }

        $data = $this->validate([
            'studentName' => ['required', 'string', 'max:150'],
            'studentNumber' => ['required', 'string', 'max:30', $studentNumberRule],
            'phone' => ['nullable', 'string', 'max:30'],
            'faculty' => ['required', 'string', 'max:150'],
            'studyProgram' => ['required', 'string', 'max:150'],
            'entryYear' => ['required', 'integer', 'min:1950', 'max:'.now()->year],
            'interests' => ['required', 'string', 'max:2000'],
            'talents' => ['required', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2048,max_height=2048'],
        ]);

        $oldPhotoPath = $profile?->photo_path;
        $newPhotoPath = $this->photo?->store('student-photos/'.$user->getKey(), 'local');

        if ($this->photo !== null && ! is_string($newPhotoPath)) {
            throw new RuntimeException('Foto gagal disimpan. Silakan coba kembali.');
        }

        $profile ??= $user->studentProfiles()->make();
        $profile->fill([
            'student_name' => $data['studentName'],
            'student_number' => $data['studentNumber'],
            'phone' => $data['phone'] ?: null,
            'faculty' => $data['faculty'],
            'study_program' => $data['studyProgram'],
            'entry_year' => $data['entryYear'],
            'interests' => $data['interests'],
            'talents' => $data['talents'],
            'bio' => $data['bio'] ?: null,
        ]);

        if (is_string($newPhotoPath)) {
            $profile->photo_path = $newPhotoPath;
        }

        $profile->save();
        $this->selectedProfileId = (int) $profile->getKey();

        if (is_string($newPhotoPath) && filled($oldPhotoPath) && ! Storage::disk('local')->delete($oldPhotoPath)) {
            logger()->warning('Unable to delete the previous student profile photo.', [
                'user_id' => $user->getKey(),
                'path' => $oldPhotoPath,
            ]);
        }

        $this->reset('photo');
        $this->resetValidation();
        session()->flash('status', $isNewProfile ? 'Data mahasiswa berhasil ditambahkan.' : 'Data mahasiswa berhasil diperbarui.');
    }

    public function render()
    {
        $profiles = Auth::user()->studentProfiles()->orderBy('student_name')->get();
        $profile = $profiles->firstWhere('id', $this->selectedProfileId);
        $checklist = [
            ['label' => 'Nama mahasiswa', 'complete' => filled($this->studentName)],
            ['label' => 'Nomor induk mahasiswa', 'complete' => filled($this->studentNumber)],
            ['label' => 'Fakultas', 'complete' => filled($this->faculty)],
            ['label' => 'Program studi', 'complete' => filled($this->studyProgram)],
            ['label' => 'Tahun masuk', 'complete' => filled($this->entryYear)],
            ['label' => 'Bidang minat', 'complete' => filled($this->interests)],
            ['label' => 'Bakat dan kemampuan', 'complete' => filled($this->talents)],
            ['label' => 'Foto profil', 'complete' => $this->photo !== null || filled($profile?->photo_path)],
        ];
        $completedItems = count(array_filter($checklist, fn (array $item): bool => $item['complete']));

        return view('livewire.student-dashboard', [
            'profile' => $profile,
            'profiles' => $profiles,
            'checklist' => $checklist,
            'completion' => (int) round($completedItems / count($checklist) * 100),
            'interestCount' => $this->countEntries($this->interests),
            'talentCount' => $this->countEntries($this->talents),
            'today' => now()->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    private function countEntries(string $value): int
    {
        $entries = preg_split('/[,;\n]+/', $value) ?: [];

        return count(array_filter($entries, fn (string $entry): bool => trim($entry) !== ''));
    }

    private function loadProfile(StudentProfile $profile): void
    {
        $this->selectedProfileId = (int) $profile->getKey();
        $this->studentName = $profile->student_name ?? '';
        $this->studentNumber = $profile->student_number;
        $this->phone = $profile->phone ?? '';
        $this->faculty = $profile->faculty;
        $this->studyProgram = $profile->study_program;
        $this->entryYear = (string) $profile->entry_year;
        $this->interests = $profile->interests;
        $this->talents = $profile->talents;
        $this->bio = $profile->bio ?? '';
        $this->reset('photo');
    }
}
