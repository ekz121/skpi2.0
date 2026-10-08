<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\SkpiRequest;
use App\Models\Submission;
use App\Models\SubmissionDecision;
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
            'rejectedCount' => SkpiRequest::where('status', 'rejected')->count(),
            'failedCount' => SkpiRequest::where('status', 'failed')->count(),
            'statusCounts' => SkpiRequest::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'recentRequests' => SkpiRequest::with('user.profile.studyProgram')->where('status', 'pending')->oldest()->limit(8)->get(),
        ]);
    }

    public function submissions(Request $request)
    {
        $query = Submission::with('user.profile.studyProgram', 'rule')->latest('submitted_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $query->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.submissions.index', ['submissions' => $query->paginate(12)->withQueryString()]);
    }

    public function showSubmission(Submission $submission)
    {
        return view('admin.submissions.show', ['submission' => $submission->load('user.profile.studyProgram', 'rule', 'decisions.admin')]);
    }

    public function decideSubmission(Request $request, Submission $submission)
    {
        abort_unless(in_array($submission->status, ['pending', 'approved'], true), 422);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'revision', 'rejected'])],
            'note' => [Rule::requiredIf(fn () => in_array($request->input('decision'), ['revision', 'rejected'], true)), 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $submission, $request) {
            $points = $data['decision'] === 'approved' ? $submission->rule->points : null;
            $submission->update([
                'status' => $data['decision'],
                'approved_points' => $points,
                'admin_note' => $data['note'] ?? null,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            SubmissionDecision::create([
                'submission_id' => $submission->id,
                'admin_id' => $request->user()->id,
                'decision' => $data['decision'],
                'note' => $data['note'] ?? null,
                'points' => $points,
            ]);

            $labels = [
                'approved' => 'Pengajuan disetujui',
                'revision' => 'Pengajuan perlu diperbaiki',
                'rejected' => 'Pengajuan ditolak',
            ];
            AppNotification::create([
                'user_id' => $submission->user_id,
                'title' => $labels[$data['decision']],
                'message' => $data['decision'] === 'approved'
                    ? 'Poin '.$points.' telah ditambahkan ke rekap Anda.'
                    : ($data['note'] ?? 'Silakan periksa detail pengajuan.'),
                'url' => route('student.submissions.show', $submission),
            ]);
        });

        return redirect()->route('admin.submissions.index')->with('success', 'Keputusan berhasil disimpan dan mahasiswa telah diberi notifikasi.');
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
