<?php

namespace Tests\Feature;

use App\Livewire\StudentDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_login_and_renders_store_readiness(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('KeranjangKu')
            ->assertSee('Selamat datang,')
            ->assertSee('Kesiapan toko')
            ->assertSee('Checklist toko')
            ->assertSee('Semua produk')
            ->assertSee('Tambah produk');
    }

    public function test_a_student_can_save_their_profile_and_private_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(StudentDashboard::class)
            ->set('studentName', 'Nadia Putri')
            ->set('studentNumber', '09031282420001')
            ->set('phone', '081234567890')
            ->set('faculty', 'Fakultas Ilmu Komputer')
            ->set('studyProgram', 'Sistem Informasi')
            ->set('entryYear', '2024')
            ->set('interests', 'Teknologi, desain')
            ->set('talents', 'Presentasi, desain antarmuka')
            ->set('bio', 'Senang belajar hal baru.')
            ->set('photo', UploadedFile::fake()->image('mahasiswa.png', 600, 600))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Data mahasiswa berhasil ditambahkan.')
            ->assertSee('100%');

        $profile = $user->studentProfiles()->firstOrFail();

        $this->assertModelExists($profile);
        $this->assertSame('Nadia Putri', $profile->student_name);
        $this->assertSame('09031282420001', $profile->student_number);
        $this->assertSame('Sistem Informasi', $profile->study_program);
        Storage::disk('local')->assertExists($profile->photo_path);

        $this->get(route('profile.photo', $profile->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_a_student_can_create_multiple_profiles_and_update_a_selected_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dashboard = Livewire::test(StudentDashboard::class)
            ->set('studentName', 'Mahasiswa Pertama')
            ->set('studentNumber', 'NIM-UNIK-101')
            ->set('faculty', 'Fakultas Ilmu Komputer')
            ->set('studyProgram', 'Sistem Informasi')
            ->set('entryYear', '2024')
            ->set('interests', 'Teknologi')
            ->set('talents', 'Presentasi')
            ->call('save')
            ->assertHasNoErrors();

        $firstProfile = $user->studentProfiles()->where('student_number', 'NIM-UNIK-101')->firstOrFail();

        $dashboard
            ->call('startNewProfile')
            ->set('studentName', 'Mahasiswa Kedua')
            ->set('studentNumber', 'NIM-UNIK-102')
            ->set('faculty', 'Fakultas Ekonomi')
            ->set('studyProgram', 'Manajemen')
            ->set('entryYear', '2023')
            ->set('interests', 'Wirausaha')
            ->set('talents', 'Komunikasi')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Data mahasiswa (2)')
            ->assertSee('Mahasiswa Pertama')
            ->assertSee('Mahasiswa Kedua');

        $dashboard
            ->call('selectProfile', $firstProfile->id)
            ->assertSet('studentName', 'Mahasiswa Pertama')
            ->set('faculty', 'Fakultas Hukum')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, $user->studentProfiles()->count());
        $this->assertDatabaseHas('student_profiles', [
            'id' => $firstProfile->id,
            'student_name' => 'Mahasiswa Pertama',
            'faculty' => 'Fakultas Hukum',
        ]);
    }

    public function test_a_student_can_delete_a_profile_and_its_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $profile = $user->studentProfiles()->create([
            'student_name' => 'Mahasiswa Hapus',
            'student_number' => 'NIM-UNIK-105',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Sistem Informasi',
            'entry_year' => 2024,
            'interests' => 'Teknologi',
            'talents' => 'Presentasi',
            'photo_path' => 'student-photos/'.$user->id.'/mahasiswa-hapus.png',
        ]);
        Storage::disk('local')->put($profile->photo_path, 'private-photo-data');
        $this->actingAs($user);

        Livewire::test(StudentDashboard::class)
            ->call('deleteProfile', $profile->id)
            ->assertHasNoErrors()
            ->assertSee('Data mahasiswa berhasil dihapus.')
            ->assertSet('selectedProfileId', null);

        $this->assertDatabaseMissing('student_profiles', ['id' => $profile->id]);
        Storage::disk('local')->assertMissing($profile->photo_path);
    }

    public function test_deleting_the_selected_profile_selects_another_profile_from_the_same_account(): void
    {
        $user = User::factory()->create();
        $profileToDelete = $user->studentProfiles()->create([
            'student_name' => 'Mahasiswa Pertama',
            'student_number' => 'NIM-UNIK-106',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Sistem Informasi',
            'entry_year' => 2024,
            'interests' => 'Teknologi',
            'talents' => 'Presentasi',
        ]);
        $remainingProfile = $user->studentProfiles()->create([
            'student_name' => 'Mahasiswa Kedua',
            'student_number' => 'NIM-UNIK-107',
            'faculty' => 'Fakultas Ekonomi',
            'study_program' => 'Manajemen',
            'entry_year' => 2023,
            'interests' => 'Wirausaha',
            'talents' => 'Komunikasi',
        ]);
        $this->actingAs($user);

        Livewire::test(StudentDashboard::class)
            ->call('deleteProfile', $profileToDelete->id)
            ->assertHasNoErrors()
            ->assertSet('selectedProfileId', $remainingProfile->id)
            ->assertSet('studentName', 'Mahasiswa Kedua');

        $this->assertDatabaseMissing('student_profiles', ['id' => $profileToDelete->id]);
        $this->assertModelExists($remainingProfile);
    }

    public function test_student_name_is_required_before_saving(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student);

        Livewire::test(StudentDashboard::class)
            ->set('studentNumber', 'NIM-UNIK-104')
            ->set('faculty', 'Fakultas Ilmu Komputer')
            ->set('studyProgram', 'Sistem Informasi')
            ->set('entryYear', '2024')
            ->set('interests', 'Teknologi')
            ->set('talents', 'Presentasi')
            ->call('save')
            ->assertHasErrors('studentName');

        $this->assertSame(0, $student->studentProfiles()->count());
    }

    public function test_a_student_cannot_reuse_another_students_nim(): void
    {
        $otherStudent = User::factory()->create();
        $otherStudent->studentProfiles()->create([
            'student_name' => 'Mahasiswa Lain',
            'student_number' => 'NIM-UNIK-001',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Sistem Informasi',
            'entry_year' => 2024,
            'interests' => 'Teknologi',
            'talents' => 'Presentasi',
        ]);

        $student = User::factory()->create();
        $this->actingAs($student);

        Livewire::test(StudentDashboard::class)
            ->set('studentName', 'Mahasiswa Baru')
            ->set('studentNumber', 'NIM-UNIK-001')
            ->set('faculty', 'Fakultas Ilmu Komputer')
            ->set('studyProgram', 'Sistem Informasi')
            ->set('entryYear', '2024')
            ->set('interests', 'Teknologi')
            ->set('talents', 'Presentasi')
            ->call('save')
            ->assertHasErrors('studentNumber');

        $this->assertDatabaseMissing('student_profiles', ['user_id' => $student->id]);
    }

    public function test_uploaded_photo_must_be_an_allowed_image(): void
    {
        Storage::fake('local');
        $student = User::factory()->create();
        $this->actingAs($student);

        Livewire::test(StudentDashboard::class)
            ->set('studentName', 'Mahasiswa Baru')
            ->set('studentNumber', 'NIM-UNIK-002')
            ->set('faculty', 'Fakultas Ilmu Komputer')
            ->set('studyProgram', 'Sistem Informasi')
            ->set('entryYear', '2024')
            ->set('interests', 'Teknologi')
            ->set('talents', 'Presentasi')
            ->set('photo', UploadedFile::fake()->create('catatan.txt', 20, 'text/plain'))
            ->call('save')
            ->assertHasErrors('photo');

        $this->assertDatabaseMissing('student_profiles', ['user_id' => $student->id]);
        $this->assertSame([], Storage::disk('local')->allFiles('student-photos/'.$student->id));
    }

    public function test_profile_photo_is_private_to_the_authenticated_student(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $profile = $owner->studentProfiles()->create([
            'student_name' => 'Pemilik Profil',
            'student_number' => 'NIM-UNIK-003',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Sistem Informasi',
            'entry_year' => 2024,
            'interests' => 'Teknologi',
            'talents' => 'Presentasi',
            'photo_path' => 'student-photos/'.$owner->id.'/mahasiswa.png',
        ]);
        Storage::disk('local')->put($profile->photo_path, 'private-photo-data');

        $this->get(route('profile.photo', $profile->id))->assertRedirect(route('login'));

        $otherStudent = User::factory()->create();

        $this->actingAs($otherStudent)
            ->get(route('profile.photo', $profile->id))
            ->assertNotFound();

        Livewire::test(StudentDashboard::class)
            ->call('selectProfile', $profile->id)
            ->assertStatus(404);
    }

    public function test_a_student_cannot_delete_another_students_profile(): void
    {
        $owner = User::factory()->create();
        $profile = $owner->studentProfiles()->create([
            'student_name' => 'Profil Privat',
            'student_number' => 'NIM-UNIK-006',
            'faculty' => 'Fakultas Ilmu Komputer',
            'study_program' => 'Sistem Informasi',
            'entry_year' => 2024,
            'interests' => 'Teknologi',
            'talents' => 'Presentasi',
        ]);
        $this->actingAs(User::factory()->create());

        Livewire::test(StudentDashboard::class)
            ->call('deleteProfile', $profile->id)
            ->assertStatus(404);

        $this->assertModelExists($profile);
    }
}
