<?php

namespace Tests\Feature;

use App\Mail\SystemTestMail;
use App\Models\Faculty;
use App\Models\StudentRegistry;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_only_admin_can_access_user_management(): void
    {
        $student = User::where('role', 'student')->firstOrFail();
        $this->actingAs($student)->get(route('admin.users.index'))->assertForbidden();

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()->assertSee('Manajemen user')->assertDontSee('Impor mahasiswa')->assertDontSee('Tambah user');
        $this->get('/admin/pengguna/create')->assertNotFound();
        $this->get('/admin/pengguna/impor')->assertNotFound();
        $this->get(route('admin.users.show', $student))->assertOk()->assertSee($student->email);
        $this->get(route('admin.users.edit', $student))->assertOk()->assertSee('Ubah user');
    }

    public function test_admin_student_directory_shows_roster_accounts_and_unlinked_accounts(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $linked = User::where('role', 'student')->whereDoesntHave('registry')->firstOrFail();
        $registry = StudentRegistry::whereNull('user_id')->firstOrFail();
        $registry->update(['user_id' => $linked->id]);
        $linked->refresh()->load('registry');
        $unlinked = User::where('role', 'student')->whereDoesntHave('registry')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.students', ['q' => $linked->registry->nim]));
        $response->assertOk()
            ->assertSee('77')
            ->assertSee($linked->registry->name)
            ->assertSee($linked->email)
            ->assertSee('Akun belum tertaut ke daftar akademik')
            ->assertSee($unlinked->email);
    }

    public function test_admin_can_send_an_email_configuration_test(): void
    {
        Mail::fake();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.email.test'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(SystemTestMail::class, fn (SystemTestMail $mail) => $mail->hasTo($admin->email));
    }

    public function test_admin_can_edit_search_and_filter_users_with_unit_mapping(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $program = StudyProgram::where('code', 'TI')->firstOrFail();
        $account = User::where('email', 'demo01@demo.polteksi.ac.id')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => $account->profile->nim, 'role' => 'student']))
            ->assertOk()->assertSee($account->name)->assertDontSee('Mahasiswa Uji Bima');

        $this->put(route('admin.users.update', $account), [
            'name' => 'Mahasiswa Kelola Baru',
            'email' => $account->email,
            'role' => 'student',
            'faculty_id' => $program->faculty_id,
            'study_program_id' => $program->id,
            'nim' => $account->profile->nim,
            'cohort' => 2025,
            'email_verified' => 1,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $account->id, 'name' => 'Mahasiswa Kelola Baru']);
        $this->assertDatabaseHas('student_profiles', ['user_id' => $account->id, 'cohort' => 2025]);
    }

    public function test_each_staff_role_has_a_safe_landing_page(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $faculty = Faculty::firstOrFail();
        $program = StudyProgram::firstOrFail();

        foreach ([
            ['program_verifier', 'verifikator@example.test', $program->id],
            ['leader', 'pimpinan@example.test', null],
            ['admin', 'admin.dua@example.test', null],
        ] as [$role, $email, $programId]) {
            User::create([
                'name' => ucfirst($role).' Uji', 'email' => $email, 'role' => $role,
                'faculty_id' => $faculty->id, 'study_program_id' => $programId,
                'password' => 'Akunbaru123', 'email_verified_at' => now(), 'is_active' => 1,
            ]);
        }

        $verifier = User::where('role', 'program_verifier')->firstOrFail();
        $this->actingAs($verifier)->get(route('staff.dashboard'))->assertOk()->assertSee('Verifikator Prodi');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_reset_password_and_inactive_account_cannot_login(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $student = User::where('role', 'student')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.password', $student), [
            'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('PasswordBaru123', $student->fresh()->password));

        $this->put(route('admin.users.status', $student), ['is_active' => 0])->assertSessionHasNoErrors();
        auth()->logout();
        $this->post(route('login.attempt'), ['email' => $student->email, 'password' => 'PasswordBaru123'])
            ->assertSessionHasErrors(['email' => 'Akun ini sedang dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.']);
    }

    public function test_delete_policy_deactivates_related_user_and_deletes_unrelated_user(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $related = User::where('role', 'student')->whereHas('submissions')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $related))->assertSessionHas('warning');
        $this->assertDatabaseHas('users', ['id' => $related->id, 'is_active' => false]);
        $this->assertDatabaseHas('submissions', ['user_id' => $related->id]);

        $unrelated = User::create([
            'name' => 'Akun Tanpa Data', 'email' => 'tanpa.data@example.test', 'role' => 'leader',
            'faculty_id' => Faculty::firstOrFail()->id, 'password' => 'Password123', 'is_active' => true,
        ]);
        $this->delete(route('admin.users.destroy', $unrelated))->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $unrelated->id]);
    }

    public function test_last_active_admin_and_current_session_are_protected(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.users.status', $admin), ['is_active' => 0])
            ->assertSessionHasErrors(['status']);
        $this->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors(['user']);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_email_and_nim_must_be_unique(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $student = User::where('role', 'student')->with('profile')->firstOrFail();
        $other = User::where('role', 'student')->whereKeyNot($student->id)->with('profile')->firstOrFail();
        $program = StudyProgram::firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $other), [
            'name' => 'Duplikat', 'email' => $student->email, 'role' => 'student',
            'faculty_id' => $program->faculty_id, 'study_program_id' => $program->id,
            'nim' => $student->profile->nim, 'cohort' => 2024,
            'email_verified' => 1, 'is_active' => 1,
        ])->assertSessionHasErrors(['email', 'nim']);
    }
}
