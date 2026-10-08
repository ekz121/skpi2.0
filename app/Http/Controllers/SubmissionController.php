<?php

namespace App\Http\Controllers;

use App\Models\ActivityRule;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->submissions()->with('rule')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('student.submissions.index', ['submissions' => $query->paginate(10)->withQueryString()]);
    }

    public function create()
    {
        return view('student.submissions.form', [
            'submission' => new Submission,
            'rules' => ActivityRule::where('is_active', true)->orderBy('category')->orderBy('subcategory')->orderByDesc('points')->get(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->persist($request, new Submission(['user_id' => $request->user()->id]));
    }

    public function show(Request $request, Submission $submission)
    {
        $this->authorizeOwner($request, $submission);

        return view('student.submissions.show', ['submission' => $submission->load('rule', 'decisions.admin')]);
    }

    public function edit(Request $request, Submission $submission)
    {
        $this->authorizeOwner($request, $submission);
        abort_unless(in_array($submission->status, ['draft', 'revision'], true), 403);

        return view('student.submissions.form', [
            'submission' => $submission,
            'rules' => ActivityRule::where('is_active', true)->orderBy('category')->orderBy('subcategory')->orderByDesc('points')->get(),
        ]);
    }

    public function update(Request $request, Submission $submission)
    {
        $this->authorizeOwner($request, $submission);
        abort_unless(in_array($submission->status, ['draft', 'revision'], true), 403);

        return $this->persist($request, $submission);
    }

    public function downloadEvidence(Request $request, Submission $submission)
    {
        abort_unless($request->user()->isAdmin() || $submission->user_id === $request->user()->id, 403);
        abort_unless($submission->evidence_path && Storage::disk('local')->exists($submission->evidence_path), 404);

        return Storage::disk('local')->download($submission->evidence_path, $submission->evidence_original_name);
    }

    private function persist(Request $request, Submission $submission)
    {
        $isSubmit = true;
        $data = $request->validate([
            'activity_rule_id' => ['required', Rule::exists('activity_rules', 'id')->where('is_active', true)],
            'activity_name' => ['required', 'string', 'max:180'],
            'organizer' => ['required', 'string', 'max:180'],
            'started_at' => ['required', 'date', 'before_or_equal:today'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'verification_url' => ['nullable', 'url', 'max:500'],
            'evidence' => [$isSubmit && ! $submission->evidence_path ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'action' => ['required', Rule::in(['submit'])],
        ]);

        if (filled($data['certificate_number'] ?? null)) {
            $duplicate = Submission::where('user_id', $request->user()->id)
                ->where('certificate_number', $data['certificate_number'])
                ->when($submission->exists, fn ($query) => $query->whereKeyNot($submission->id))->exists();
            if ($duplicate) {
                return back()->withErrors(['certificate_number' => 'Nomor sertifikat ini sudah pernah diajukan.'])->withInput();
            }
        }

        $rule = ActivityRule::findOrFail($data['activity_rule_id']);
        unset($data['action'], $data['evidence']);
        $data['estimated_points'] = $rule->points;
        $data['status'] = 'approved';
        $data['approved_points'] = $rule->points;
        $data['submitted_at'] = now();
        $data['verified_at'] = now();
        $data['verified_by'] = null;
        $data['admin_note'] = null;

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('evidence/'.$request->user()->id);
            $data['evidence_original_name'] = $request->file('evidence')->getClientOriginalName();
        }

        $submission->fill($data);
        $submission->user_id = $request->user()->id;
        $submission->save();

        return redirect()->route('student.submissions.show', $submission)
            ->with('success', 'Sertifikat tersimpan dan langsung masuk ke rekap kegiatan Anda.');
    }

    private function authorizeOwner(Request $request, Submission $submission): void
    {
        abort_unless($submission->user_id === $request->user()->id, 403);
    }
}
