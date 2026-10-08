<?php

namespace App\Http\Controllers;

use App\Models\ActivityRule;
use App\Models\SkpiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $approvedPoints = $user->approvedPoints();
        $counts = $user->submissions()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');
        $categoryPoints = $user->submissions()
            ->join('activity_rules', 'submissions.activity_rule_id', '=', 'activity_rules.id')
            ->where('submissions.status', 'approved')
            ->select('activity_rules.category', DB::raw('sum(submissions.approved_points) as total'))
            ->groupBy('activity_rules.category')->pluck('total', 'category');

        return view('student.dashboard', [
            'approvedPoints' => $approvedPoints,
            'counts' => $counts,
            'categoryPoints' => $categoryPoints,
            'recentSubmissions' => $user->submissions()->with('rule')->latest()->limit(5)->get(),
            'skpi' => $user->skpiRequests()->latest()->first(),
        ]);
    }

    public function profile(Request $request)
    {
        return view('student.profile', ['profile' => $request->user()->profile()->with('studyProgram')->firstOrFail()]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'birthplace' => ['required', 'string', 'max:100'],
            'birthdate' => ['required', 'date', 'before:today'],
        ]);
        $request->user()->profile()->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function recap(Request $request)
    {
        $submissions = $request->user()->submissions()->with('rule')
            ->where('status', 'approved')->latest('verified_at')->get();

        return view('student.recap', [
            'submissions' => $submissions,
            'totalPoints' => (int) $submissions->sum('approved_points'),
            'byCategory' => $submissions->groupBy(fn ($item) => $item->rule->category)
                ->map(fn ($items) => $items->sum('approved_points')),
        ]);
    }

    public function guide(Request $request)
    {
        $rules = ActivityRule::where('is_active', true)->orderBy('category')->orderBy('subcategory')->orderByDesc('points')->get();

        return view('student.guide', ['rules' => $rules->groupBy('category')]);
    }

    public function skpi(Request $request)
    {
        $user = $request->user()->load('profile.studyProgram');

        return view('student.skpi', [
            'points' => $user->approvedPoints(),
            'profileComplete' => $user->profile?->isComplete() ?? false,
            'skpi' => $user->skpiRequests()->latest()->first(),
            'approvedSubmissions' => $user->submissions()->with('rule')->where('status', 'approved')->get(),
        ]);
    }

    public function requestSkpi(Request $request)
    {
        $user = $request->user()->load('profile');
        $identity = $request->validate([
            'birthplace' => ['required', 'string', 'max:100'],
            'birthdate' => ['required', 'date', 'before:today'],
        ]);
        $user->profile->update($identity);
        $user->load('profile');
        if (! $user->submissions()->where('status', 'approved')->exists()) {
            return back()->withErrors(['skpi' => 'Unggah sedikitnya satu sertifikat sebelum mengajukan SKPI.']);
        }
        if (! $user->profile?->isComplete()) {
            return back()->withErrors(['skpi' => 'Lengkapi seluruh data dokumen pada profil sebelum mengajukan SKPI.']);
        }

        $activeRequest = $user->skpiRequests()->whereIn('status', ['pending', 'issued'])->exists();
        if ($activeRequest) {
            return back()->withErrors(['skpi' => 'Anda sudah memiliki pengajuan SKPI aktif.']);
        }

        $user->skpiRequests()->create(['status' => 'pending']);

        return back()->with('success', 'Pengajuan SKPI berhasil dikirim untuk diperiksa admin.');
    }

    public function downloadSkpi(Request $request, SkpiRequest $skpiRequest)
    {
        abort_unless($skpiRequest->user_id === $request->user()->id && $skpiRequest->status === 'issued', 403);
        $path = $skpiRequest->docx_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'SKPI-'.$request->user()->profile->nim.'.docx');
    }
}
