<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\ActivityRule;
use App\Models\SkpiRequest;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SkpiStatusNotification;
use App\Services\SkpiDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'pendingCount' => SkpiRequest::where('status', 'pending')->count(),
            'issuedCount' => SkpiRequest::where('status', 'issued')->count(),
            'newCertificateCount' => Submission::whereNull('admin_checked_at')->count(),
            'checkedCertificateCount' => Submission::whereNotNull('admin_checked_at')->count(),
            'recentRequests' => SkpiRequest::with('user.profile.studyProgram')->where('status', 'pending')->oldest()->limit(8)->get(),
            'recentCertificates' => Submission::with('user.profile.studyProgram', 'rule')->whereNull('admin_checked_at')->latest('submitted_at')->limit(8)->get(),
        ]);
    }

    public function submissions(Request $request)
    {
        $query = Submission::with('user.profile.studyProgram', 'rule', 'checker')->latest('submitted_at');
        if ($request->input('review') === 'new') {
            $query->whereNull('admin_checked_at');
        } elseif ($request->input('review') === 'checked') {
            $query->whereNotNull('admin_checked_at');
        }
        if ($request->filled('q')) {
            $term = '%'.(string) $request->string('q').'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('activity_name', 'like', $term)
                    ->orWhere('certificate_number', 'like', $term)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $term)
                        ->orWhereHas('profile', fn ($profile) => $profile->where('nim', 'like', $term)));
            });
        }
        if ($request->filled('category')) {
            $query->whereHas('rule', fn ($rule) => $rule->where('category', $request->string('category')));
        }

        return view('admin.submissions.index', [
            'submissions' => $query->paginate(25)->withQueryString(),
            'categories' => ActivityRule::where('is_active', true)->distinct()->orderBy('category')->pluck('category'),
            'newCount' => Submission::whereNull('admin_checked_at')->count(),
        ]);
    }

    public function showSubmission(Submission $submission)
    {
        return view('admin.submissions.show', ['submission' => $submission->load('user.profile.studyProgram', 'rule', 'checker')]);
    }

    public function createSubmission()
    {
        return view('admin.submissions.form', [
            'submission' => new Submission,
            'students' => User::where('role', 'student')->with('profile.studyProgram')->orderBy('name')->get(),
            'rules' => ActivityRule::where('is_active', true)->orderBy('category')->orderBy('subcategory')->orderByDesc('points')->get(),
        ]);
    }

    public function storeSubmission(Request $request)
    {
        return $this->persistSubmission($request, new Submission);
    }

    public function editSubmission(Submission $submission)
    {
        return view('admin.submissions.form', [
            'submission' => $submission->load('user.profile.studyProgram'),
            'students' => User::where('role', 'student')->with('profile.studyProgram')->orderBy('name')->get(),
            'rules' => ActivityRule::where('is_active', true)->orderBy('category')->orderBy('subcategory')->orderByDesc('points')->get(),
        ]);
    }

    public function updateSubmission(Request $request, Submission $submission)
    {
        return $this->persistSubmission($request, $submission);
    }

    public function checkSubmission(Request $request, Submission $submission)
    {
        $submission->update([
            'admin_checked_by' => $request->user()->id,
            'admin_checked_at' => now(),
        ]);

        return back()->with('success', 'Sertifikat ditandai sudah diperiksa. Status mahasiswa tetap langsung aktif.');
    }

    public function destroySubmission(Submission $submission)
    {
        $userId = $submission->user_id;
        $evidencePath = $submission->evidence_path;
        $submission->delete();
        if ($evidencePath) {
            Storage::disk('local')->delete($evidencePath);
        }
        AppNotification::create([
            'user_id' => $userId,
            'title' => 'Data sertifikat dihapus admin',
            'message' => 'Satu data sertifikat dihapus dari rekap. Hubungi admin jika Anda memerlukan penjelasan.',
            'url' => route('student.submissions.index'),
        ]);

        return redirect()->route('admin.submissions.index')->with('success', 'Data sertifikat dan berkas buktinya berhasil dihapus.');
    }

    private function persistSubmission(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'student')],
            'activity_rule_id' => ['required', Rule::exists('activity_rules', 'id')->where('is_active', true)],
            'activity_name' => ['required', 'string', 'max:180'],
            'organizer' => ['required', 'string', 'max:180'],
            'started_at' => ['required', 'date', 'before_or_equal:today'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'verification_url' => ['nullable', 'url', 'max:500'],
            'evidence' => [$submission->evidence_path ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if (filled($data['certificate_number'] ?? null)) {
            $duplicate = Submission::where('user_id', $data['user_id'])
                ->where('certificate_number', $data['certificate_number'])
                ->when($submission->exists, fn ($query) => $query->whereKeyNot($submission->id))
                ->exists();
            if ($duplicate) {
                return back()->withErrors(['certificate_number' => 'Nomor sertifikat ini sudah terdaftar untuk mahasiswa tersebut.'])->withInput();
            }
        }

        $rule = ActivityRule::findOrFail($data['activity_rule_id']);
        $oldEvidence = $submission->evidence_path;
        unset($data['evidence']);
        $data['status'] = 'approved';
        $data['estimated_points'] = $rule->points;
        $data['approved_points'] = $rule->points;
        $data['submitted_at'] = $submission->submitted_at ?: now();
        $data['verified_at'] = $submission->verified_at ?: now();
        $data['verified_by'] = null;
        $data['admin_note'] = null;
        $data['admin_checked_by'] = $request->user()->id;
        $data['admin_checked_at'] = now();

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence/'.$data['user_id']);
            $data['evidence_original_name'] = $request->file('evidence')->getClientOriginalName();
        }

        $wasExisting = $submission->exists;
        $submission->fill($data)->save();
        if ($request->hasFile('evidence') && $oldEvidence && $oldEvidence !== $submission->evidence_path) {
            Storage::disk('local')->delete($oldEvidence);
        }

        AppNotification::create([
            'user_id' => $submission->user_id,
            'title' => $wasExisting ? 'Data sertifikat diperbarui admin' : 'Sertifikat ditambahkan admin',
            'message' => 'Sertifikat tetap aktif dan dapat digunakan untuk pengajuan SKPI.',
            'url' => route('student.submissions.show', $submission),
        ]);

        return redirect()->route('admin.submissions.show', $submission)
            ->with('success', $wasExisting ? 'Data sertifikat berhasil diperbarui.' : 'Sertifikat berhasil ditambahkan dan langsung aktif.');
    }

    public function students(Request $request)
    {
        $students = User::where('role', 'student')
            ->with('profile.studyProgram')
            ->withCount(['submissions as approved_submissions_count' => fn ($query) => $query->where('status', 'approved')])
            ->withSum(['submissions as approved_points' => fn ($query) => $query->where('status', 'approved')], 'approved_points')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.students', compact('students'));
    }

    public function skpiRequests(Request $request)
    {
        $query = SkpiRequest::with('user.profile.studyProgram')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.skpi.index', ['requests' => $query->paginate(100)->withQueryString()]);
    }

    public function showSkpi(SkpiRequest $skpiRequest)
    {
        $skpiRequest->load('user.profile.studyProgram');

        return view('admin.skpi.show', [
            'skpi' => $skpiRequest,
            'points' => $skpiRequest->user->approvedPoints(),
            'activities' => $skpiRequest->user->submissions()->with('rule')->where('status', 'approved')->get(),
        ]);
    }

    public function updateSkpi(Request $request, SkpiRequest $skpiRequest, SkpiDocumentService $documents)
    {
        abort_unless(in_array($skpiRequest->status, ['pending', 'rejected', 'failed'], true), 422);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['issue', 'reject'])],
        ]);

        if ($data['decision'] === 'reject') {
            $this->rejectSkpi($skpiRequest, $request->user());

            return redirect()->route('admin.skpi.index')->with('success', 'Permintaan SKPI ditolak.');
        }

        try {
            $this->issueSkpi($skpiRequest, $request->user(), $documents);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            Log::error('SKPI generation failed', ['request_id' => $skpiRequest->id, 'error' => $exception->getMessage()]);
            $skpiRequest->update(['status' => 'failed', 'admin_note' => null]);

            return back()->withErrors(['document' => 'Dokumen Word atau PDF gagal dibuat. Silakan ulangi penerbitan.']);
        }

        return redirect()->route('admin.skpi.index')->with('success', 'SKPI Word dan PDF berhasil diterbitkan.');
    }

    public function bulkSkpi(Request $request, SkpiDocumentService $documents)
    {
        $data = $request->validate([
            'request_ids' => ['required', 'array', 'min:1'],
            'request_ids.*' => ['integer', 'exists:skpi_requests,id'],
            'action' => ['required', Rule::in(['issue', 'reject', 'download'])],
        ]);
        $requests = SkpiRequest::with('user.profile.studyProgram')->whereIn('id', $data['request_ids'])->get();

        if ($data['action'] === 'download') {
            return $this->downloadSkpiArchive($requests);
        }

        $processed = 0;
        $failed = [];
        foreach ($requests as $item) {
            if (! in_array($item->status, ['pending', 'rejected', 'failed'], true)) {
                continue;
            }
            try {
                if ($data['action'] === 'issue') {
                    $this->issueSkpi($item, $request->user(), $documents);
                } else {
                    $this->rejectSkpi($item, $request->user());
                }
                $processed++;
            } catch (\Throwable $exception) {
                Log::warning('Bulk SKPI action failed', ['request_id' => $item->id, 'error' => $exception->getMessage()]);
                $failed[] = $item->user->name;
            }
        }

        $message = $processed.' pengajuan berhasil '.($data['action'] === 'issue' ? 'diterbitkan' : 'ditolak').'.';
        if ($failed !== []) {
            return back()->with('success', $message)->withErrors(['bulk' => 'Tidak dapat memproses: '.implode(', ', $failed).'. Periksa kelengkapan datanya.']);
        }

        return back()->with('success', $message);
    }

    public function downloadSkpi(SkpiRequest $skpiRequest, string $format)
    {
        abort_unless($skpiRequest->status === 'issued' && in_array($format, ['pdf', 'docx'], true), 404);
        $path = $format === 'pdf' ? $skpiRequest->pdf_path : $skpiRequest->docx_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        $nim = $skpiRequest->user->profile?->nim ?: $skpiRequest->id;

        return Storage::disk('local')->download($path, 'SKPI-'.$nim.'.'.$format);
    }

    private function issueSkpi(SkpiRequest $skpiRequest, User $admin, SkpiDocumentService $documents): void
    {
        $user = $skpiRequest->user()->with('profile.studyProgram')->firstOrFail();
        $errors = [];
        if (! $user->submissions()->where('status', 'approved')->exists()) {
            $errors['skpi'] = 'Mahasiswa belum memiliki sertifikat yang tersimpan.';
        } elseif (! $user->profile?->isComplete()) {
            $errors['skpi'] = 'Biodata dokumen mahasiswa belum lengkap.';
        } elseif (empty($user->profile->studyProgram->learning_outcomes)) {
            $errors['skpi'] = 'Capaian pembelajaran resmi program studi belum dikonfigurasi.';
        } elseif (! config('skpi.signatory_name') || ! config('skpi.signatory_nidn')) {
            $errors['skpi'] = 'Nama atau NIDN pejabat pengesah belum dikonfigurasi.';
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $files = $documents->generate($skpiRequest);
        DB::transaction(function () use ($skpiRequest, $admin, $files, $user) {
            $skpiRequest->update([
                'status' => 'issued',
                'reviewed_by' => $admin->id,
                'admin_note' => null,
                'document_number' => $files['number'],
                'snapshot' => $files['snapshot'],
                'pdf_path' => $files['pdfPath'],
                'docx_path' => $files['docxPath'],
                'issued_at' => now(),
            ]);
            AppNotification::create([
                'user_id' => $user->id,
                'title' => 'SKPI telah diterbitkan',
                'message' => 'Dokumen Word dan PDF tersedia dan dapat diunduh dari akun Anda.',
                'url' => route('student.skpi'),
            ]);
        });
        try {
            $skpiRequest->refresh();
            $user->notify(new SkpiStatusNotification($skpiRequest, 'Dokumen Word dan PDF telah tersedia di akun Anda.'));
        } catch (\Throwable $exception) {
            Log::warning('SKPI issued email could not be sent', ['request_id' => $skpiRequest->id, 'error' => $exception->getMessage()]);
        }
    }

    private function rejectSkpi(SkpiRequest $skpiRequest, User $admin): void
    {
        $skpiRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'admin_note' => null,
        ]);
        AppNotification::create([
            'user_id' => $skpiRequest->user_id,
            'title' => 'Permintaan SKPI ditolak',
            'message' => 'Permintaan SKPI belum dapat diterbitkan. Periksa kembali data profil dan sertifikat Anda.',
            'url' => route('student.skpi'),
        ]);
        try {
            $skpiRequest->refresh();
            $skpiRequest->user->notify(new SkpiStatusNotification($skpiRequest, 'Permintaan SKPI belum dapat diterbitkan. Periksa kembali data profil dan sertifikat Anda.'));
        } catch (\Throwable $exception) {
            Log::warning('SKPI rejection email could not be sent', ['request_id' => $skpiRequest->id, 'error' => $exception->getMessage()]);
        }
    }

    private function downloadSkpiArchive($requests)
    {
        $issued = $requests->where('status', 'issued');
        if ($issued->isEmpty()) {
            return back()->withErrors(['bulk' => 'Pilih sedikitnya satu SKPI yang sudah terbit.']);
        }

        $directory = storage_path('app/private/tmp');
        File::ensureDirectoryExists($directory);
        $archivePath = $directory.DIRECTORY_SEPARATOR.'SKPI-'.now()->format('Ymd-His').'-'.Str::random(8).'.zip';
        $archive = new ZipArchive;
        if ($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['bulk' => 'Arsip unduhan tidak dapat dibuat.']);
        }
        foreach ($issued as $item) {
            $nim = $item->user->profile?->nim ?: $item->id;
            foreach (['pdf' => $item->pdf_path, 'docx' => $item->docx_path] as $extension => $path) {
                if ($path && Storage::disk('local')->exists($path)) {
                    $archive->addFromString('SKPI-'.$nim.'-'.$item->id.'.'.$extension, Storage::disk('local')->get($path));
                }
            }
        }
        $archive->close();

        return response()->download($archivePath, 'Dokumen-SKPI.zip')->deleteFileAfterSend(true);
    }
}
