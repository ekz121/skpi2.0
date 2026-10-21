<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\StudentProfile;
use App\Models\StudentRegistry;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['faculty', 'studyProgram', 'profile.studyProgram'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('nim', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->when($request->filled('faculty_id'), fn ($query) => $query->where('faculty_id', $request->integer('faculty_id')))
            ->when($request->filled('study_program_id'), fn ($query) => $query->where('study_program_id', $request->integer('study_program_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', $this->formOptions() + compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['faculty', 'studyProgram', 'profile.studyProgram', 'deactivatedBy'])
            ->loadCount(['submissions', 'skpiRequests', 'verifiedSubmissions', 'checkedSubmissions', 'submissionDecisions', 'reviewedSkpiRequests', 'skpiWithdrawals']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load(['profile', 'faculty', 'studyProgram']);

        return view('admin.users.form', $this->formOptions() + compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);
        if ($user->isStudent() && $data['role'] !== 'student' && $user->hasRelatedAcademicData()) {
            throw ValidationException::withMessages([
                'role' => 'Role mahasiswa yang sudah memiliki data akademik tidak dapat diubah. Nonaktifkan akun jika tidak lagi digunakan.',
            ]);
        }
        if ($user->is($request->user()) && (! $data['is_active'] || $data['role'] !== 'admin')) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat mengubah role atau menonaktifkan akun sendiri.',
            ]);
        }
        $this->guardLastAdmin($user, $data['role'], $data['is_active']);

        DB::transaction(function () use ($user, $data) {
            $program = isset($data['study_program_id']) ? StudyProgram::find($data['study_program_id']) : null;
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'faculty_id' => $program?->faculty_id ?? ($data['faculty_id'] ?? null),
                'study_program_id' => $program?->id,
                'is_active' => $data['is_active'],
                'email_verified_at' => $data['email_verified'] ? ($user->email_verified_at ?? now()) : null,
                'deactivated_at' => $data['is_active'] ? null : ($user->deactivated_at ?? now()),
                'deactivated_by' => $data['is_active'] ? null : auth()->id(),
            ]);
            $this->syncStudentData($user, $data);
        });

        return redirect()->route('admin.users.show', $user)->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();

        return back()->with('success', 'Password '.$user->name.' berhasil diatur ulang.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $active = (bool) $data['is_active'];
        if ($user->is($request->user()) && ! $active) {
            return back()->withErrors(['status' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }
        $this->guardLastAdmin($user, $user->role, $active);
        $user->update([
            'is_active' => $active,
            'deactivated_at' => $active ? null : now(),
            'deactivated_by' => $active ? null : $request->user()->id,
        ]);

        return back()->with('success', $active ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        $this->guardLastAdmin($user, $user->role, false);

        if ($user->hasRelatedAcademicData()) {
            $user->update(['is_active' => false, 'deactivated_at' => now(), 'deactivated_by' => $request->user()->id]);

            return redirect()->route('admin.users.show', $user)
                ->with('warning', 'Akun memiliki data terkait sehingga tidak dihapus permanen. Akun sudah dinonaktifkan.');
        }

        DB::transaction(function () use ($user) {
            $user->registry?->update(['user_id' => null]);
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Akun tanpa data terkait berhasil dihapus permanen.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'nim' => $request->filled('nim') ? Str::upper(trim((string) $request->input('nim'))) : null,
        ]);
        $student = $request->input('role') === 'student';
        $programRequired = in_array($request->input('role'), ['student', 'program_verifier'], true);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'faculty_id' => [$request->input('role') === 'leader' ? 'required' : 'nullable', 'exists:faculties,id'],
            'study_program_id' => [$programRequired ? 'required' : 'nullable', 'exists:study_programs,id'],
            'nim' => [$student ? 'required' : 'nullable', 'string', 'max:30', Rule::unique('student_profiles', 'nim')->ignore($user?->profile?->id)],
            'cohort' => [$student ? 'required' : 'nullable', 'integer', 'between:2000,'.(now()->year + 1)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'email_verified' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
        $validator->after(function ($validator) use ($request, $student, $user) {
            $program = StudyProgram::find($request->input('study_program_id'));
            if ($program && $request->filled('faculty_id') && (int) $request->faculty_id !== (int) $program->faculty_id) {
                $validator->errors()->add('study_program_id', 'Program studi tidak berada pada fakultas yang dipilih.');
            }
            if ($student && StudentRegistry::where('nim', $request->input('nim'))
                ->whereNotNull('user_id')->when($user, fn ($query) => $query->where('user_id', '!=', $user->id))->exists()) {
                $validator->errors()->add('nim', 'NIM sudah terhubung ke akun lain.');
            }
        });

        return $validator->validate();
    }

    private function syncStudentData(User $user, array $data): void
    {
        if ($data['role'] !== 'student') {
            if ($user->profile && ! $user->hasRelatedAcademicData()) {
                $user->registry?->update(['user_id' => null]);
                $user->profile->delete();
            }

            return;
        }

        $program = StudyProgram::findOrFail($data['study_program_id']);
        $profile = StudentProfile::updateOrCreate(['user_id' => $user->id], [
            'study_program_id' => $program->id,
            'nim' => $data['nim'],
            'cohort' => $data['cohort'],
            'graduation_year' => $user->profile?->graduation_year ?? 2026,
            'diploma_number' => $user->profile?->diploma_number,
            'academic_title' => $program->academic_title,
        ]);
        $currentRegistry = StudentRegistry::where('user_id', $user->id)->first();
        $registry = StudentRegistry::where('nim', $data['nim'])->first();
        if ($currentRegistry && $registry && ! $currentRegistry->is($registry)) {
            $currentRegistry->update(['user_id' => null]);
        }
        $registry ??= $currentRegistry;
        if ($registry) {
            $registry->update([
                'user_id' => $user->id, 'name' => $data['name'], 'study_program_id' => $program->id,
                'nim' => $data['nim'], 'cohort' => $data['cohort'],
            ]);
        }
        $user->setRelation('profile', $profile);
    }

    private function guardLastAdmin(User $user, string $nextRole, bool $nextActive): void
    {
        if ($user->role === 'admin' && $user->is_active && ($nextRole !== 'admin' || ! $nextActive)
            && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Minimal satu akun admin harus tetap aktif.']);
        }
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'roles' => User::ROLES,
            'faculties' => Faculty::orderBy('name')->get(),
            'studyPrograms' => StudyProgram::with('faculty')->orderBy('name')->get(),
        ];
    }
}
