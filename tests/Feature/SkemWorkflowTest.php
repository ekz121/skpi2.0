<?php

namespace Tests\Feature;

use App\Models\ActivityRule;
use App\Models\AppNotification;
use App\Models\SkpiRequest;
use App\Models\StudentProfile;
use App\Models\StudyProgram;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SkpiStatusNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SkemWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_the_shared_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Selamat datang kembali')->assertDontSee('data-fill-login', false);
    }

    public function test_login_redirects_by_role_stored_on_the_account(): void
    {
        $this->post(route('login.attempt'), ['email' => 'mahasiswa@demo.polteksi.ac.id', 'password' => 'demo12345'])
            ->assertRedirect(route('student.dashboard'));
        auth()->logout();
        $this->post(route('login.attempt'), ['email' => 'admin@polteksi.ac.id', 'password' => 'polteksi123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_registration_sends_email_verification_notification(): void
    {
        Notification::fake();
        $program = StudyProgram::firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Mahasiswa Baru',
            'nim' => '202699001',
            'study_program_id' => $program->id,
            'cohort' => 2026,
            'email' => 'baru@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'baru@example.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();

        $this->post(route('password.email'), ['email' => $student->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($student, ResetPassword::class);
    }

    public function test_only_approved_submissions_contribute_to_student_points(): void
    {
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $this->assertSame(590, $student->approvedPoints());
        $this->assertSame(0, $student->submissions()->where('status', 'pending')->count());
    }

    public function test_admin_can_mark_an_auto_approved_certificate_as_checked(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $submission = Submission::where('status', 'approved')->firstOrFail();
        $points = $submission->approved_points;

        $this->actingAs($admin)->put(route('admin.submissions.check', $submission))
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame('approved', $submission->status);
        $this->assertSame($points, $submission->approved_points);
        $this->assertSame($admin->id, $submission->admin_checked_by);
        $this->assertNotNull($submission->admin_checked_at);
    }

    public function test_admin_can_create_update_and_delete_certificate_data(): void
    {
        Storage::fake('local');
        $admin = User::where('role', 'admin')->firstOrFail();
        $rule = ActivityRule::firstOrFail();
        $student = User::where('role', 'student')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.submissions.store'), [
            'user_id' => $student->id,
            'activity_rule_id' => $rule->id,
            'activity_name' => 'Sertifikat dari admin',
            'organizer' => 'Panitia kampus',
            'started_at' => now()->subDay()->format('Y-m-d'),
            'certificate_number' => 'ADMIN-CERT-001',
            'evidence' => UploadedFile::fake()->create('bukti-admin.pdf', 120, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $submission = Submission::where('certificate_number', 'ADMIN-CERT-001')->firstOrFail();
        $this->assertSame('approved', $submission->status);
        $this->assertNotNull($submission->admin_checked_at);

        $this->put(route('admin.submissions.update', $submission), [
            'user_id' => $student->id,
            'activity_rule_id' => $rule->id,
            'activity_name' => 'Sertifikat diperbarui admin',
            'organizer' => 'Panitia kampus',
            'started_at' => now()->subDay()->format('Y-m-d'),
            'certificate_number' => 'ADMIN-CERT-001',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'activity_name' => 'Sertifikat diperbarui admin', 'status' => 'approved']);

        $evidencePath = $submission->fresh()->evidence_path;
        $this->delete(route('admin.submissions.destroy', $submission))->assertRedirect(route('admin.submissions.index'));
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        Storage::disk('local')->assertMissing($evidencePath);
    }

    public function test_student_cannot_open_another_students_submission(): void
    {
        $other = User::create(['name' => 'Mahasiswa Kedua', 'email' => 'kedua@example.test', 'role' => 'student', 'email_verified_at' => now(), 'password' => 'password123']);
        StudentProfile::create(['user_id' => $other->id, 'study_program_id' => StudyProgram::firstOrFail()->id, 'nim' => 'TEST-002', 'cohort' => 2024]);
        $rule = ActivityRule::firstOrFail();
        $submission = Submission::create(['user_id' => $other->id, 'activity_rule_id' => $rule->id, 'activity_name' => 'Pengajuan milik akun lain', 'organizer' => 'Penguji', 'started_at' => now()->subDay(), 'status' => 'pending', 'estimated_points' => $rule->points]);
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $this->actingAs($student)->get(route('student.submissions.show', $submission))->assertForbidden();
    }

    public function test_notifications_can_be_deleted_only_by_their_owner(): void
    {
        $student = User::where('role', 'student')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();
        $own = AppNotification::create(['user_id' => $student->id, 'title' => 'Milik mahasiswa', 'message' => 'Pesan uji']);
        $other = AppNotification::create(['user_id' => $admin->id, 'title' => 'Milik admin', 'message' => 'Pesan uji']);

        $this->actingAs($student)->delete(route('notifications.destroy', $other))->assertForbidden();
        $this->delete(route('notifications.destroy', $own))->assertRedirect();
        $this->assertDatabaseMissing('app_notifications', ['id' => $own->id]);
        $this->assertDatabaseHas('app_notifications', ['id' => $other->id]);

        AppNotification::create(['user_id' => $student->id, 'title' => 'Pertama', 'message' => 'Pesan uji']);
        AppNotification::create(['user_id' => $student->id, 'title' => 'Kedua', 'message' => 'Pesan uji']);
        $this->delete(route('notifications.destroy-all'))->assertRedirect();
        $this->assertDatabaseMissing('app_notifications', ['user_id' => $student->id]);
        $this->assertDatabaseHas('app_notifications', ['id' => $other->id]);
    }

    public function test_certificate_upload_is_immediately_stored_and_approved(): void
    {
        Storage::fake('local');
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $rule = ActivityRule::firstOrFail();

        $this->actingAs($student)->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('Lanjutkan')
            ->assertSee('Simpan sertifikat')
            ->assertDontSee('Simpan draf');

        $this->post(route('student.submissions.store'), [
            'activity_rule_id' => $rule->id,
            'activity_name' => 'Sertifikat pengujian',
            'organizer' => 'Panitia pengujian',
            'started_at' => now()->subDay()->format('Y-m-d'),
            'certificate_number' => 'TEST-CERT-001',
            'evidence' => UploadedFile::fake()->create('sertifikat.pdf', 128, 'application/pdf'),
            'action' => 'submit',
        ])->assertSessionHasNoErrors();

        $submission = Submission::where('certificate_number', 'TEST-CERT-001')->firstOrFail();
        $this->assertSame('approved', $submission->status);
        $this->assertSame($rule->points, $submission->approved_points);
        Storage::disk('local')->assertExists($submission->evidence_path);
    }

    public function test_eligible_student_can_request_and_admin_can_issue_pdf(): void
    {
        Storage::fake('local');
        Notification::fake();
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $this->actingAs($student)->post(route('student.skpi.request'))->assertSessionHasNoErrors();
        $skpi = SkpiRequest::where('user_id', $student->id)->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.skpi.update', $skpi), ['decision' => 'issue'])
            ->assertRedirect(route('admin.skpi.index'));
        $skpi->refresh();
        $this->assertSame('issued', $skpi->status);
        $this->assertNotNull($skpi->document_number);
        Storage::disk('local')->assertExists($skpi->pdf_path);
        Storage::disk('local')->assertExists($skpi->docx_path);
        Notification::assertSentTo($student, SkpiStatusNotification::class);
    }

    public function test_one_low_point_certificate_is_enough_to_request_skpi(): void
    {
        $program = StudyProgram::where('code', 'TI')->firstOrFail();
        $student = User::create(['name' => 'Mahasiswa Satu Sertifikat', 'email' => 'satu@example.test', 'role' => 'student', 'email_verified_at' => now(), 'password' => 'password123']);
        $student->forceFill(['email_verified_at' => now()])->save();
        StudentProfile::create([
            'user_id' => $student->id, 'study_program_id' => $program->id, 'nim' => 'TEST-SATU', 'cohort' => 2025,
            'birthplace' => 'Gresik', 'birthdate' => '2005-01-02', 'graduation_year' => 2026,
            'diploma_number' => 'IJAZAH-TEST-SATU', 'academic_title' => 'A.Md.Kom.',
        ]);
        $rule = ActivityRule::where('points', 10)->firstOrFail();
        Submission::create([
            'user_id' => $student->id, 'activity_rule_id' => $rule->id, 'activity_name' => 'Webinar satu hari',
            'organizer' => 'Penyelenggara Uji', 'started_at' => now()->subDay(), 'status' => 'approved',
            'estimated_points' => 10, 'approved_points' => 10, 'submitted_at' => now(), 'verified_at' => now(),
        ]);

        $this->actingAs($student)->post(route('student.skpi.request'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('skpi_requests', ['user_id' => $student->id, 'status' => 'pending']);
    }

    public function test_admin_can_issue_multiple_skpi_requests_in_one_action(): void
    {
        Storage::fake('local');
        Notification::fake();
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $requests = collect([
            SkpiRequest::create(['user_id' => $student->id, 'status' => 'pending']),
            SkpiRequest::create(['user_id' => $student->id, 'status' => 'pending']),
        ]);
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.skpi.bulk'), [
            'request_ids' => $requests->pluck('id')->all(),
            'action' => 'issue',
        ])->assertSessionHasNoErrors();

        foreach ($requests as $item) {
            $item->refresh();
            $this->assertSame('issued', $item->status);
            Storage::disk('local')->assertExists($item->pdf_path);
            Storage::disk('local')->assertExists($item->docx_path);
        }
    }

    public function test_admin_can_download_many_issued_documents_as_one_zip(): void
    {
        Storage::fake('local');
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $first = SkpiRequest::create(['user_id' => $student->id, 'status' => 'issued', 'pdf_path' => 'skpi/first.pdf', 'docx_path' => 'skpi/first.docx', 'issued_at' => now()]);
        $second = SkpiRequest::create(['user_id' => $student->id, 'status' => 'issued', 'pdf_path' => 'skpi/second.pdf', 'docx_path' => 'skpi/second.docx', 'issued_at' => now()]);
        foreach (['skpi/first.pdf', 'skpi/first.docx', 'skpi/second.pdf', 'skpi/second.docx'] as $path) {
            Storage::disk('local')->put($path, 'dokumen uji');
        }

        $admin = User::where('role', 'admin')->firstOrFail();
        $response = $this->actingAs($admin)->post(route('admin.skpi.bulk'), [
            'request_ids' => [$first->id, $second->id],
            'action' => 'download',
        ]);

        $response->assertOk()->assertDownload('Dokumen-SKPI.zip');
    }

    public function test_all_student_pages_render_without_server_errors(): void
    {
        $student = User::where('email', 'mahasiswa@demo.polteksi.ac.id')->firstOrFail();
        $submission = $student->submissions()->firstOrFail();
        $this->actingAs($student);

        foreach ([
            route('student.dashboard'), route('student.profile'), route('student.submissions.index'),
            route('student.submissions.create'), route('student.submissions.show', $submission),
            route('student.recap'), route('student.guide'), route('student.skpi'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_all_admin_pages_render_without_server_errors(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $submission = Submission::firstOrFail();
        $this->actingAs($admin);

        foreach ([
            route('admin.dashboard'), route('admin.submissions.index'), route('admin.submissions.show', $submission),
            route('admin.submissions.create'), route('admin.submissions.edit', $submission),
            route('admin.students'), route('admin.skpi.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
