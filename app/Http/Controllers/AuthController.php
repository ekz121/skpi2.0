<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\StudentRegistry;
use App\Models\User;
use App\Services\MailConfiguration;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $account = User::where('email', $credentials['email'])->first();
        if ($account && Hash::check($credentials['password'], $account->password) && ! $account->is_active) {
            return back()->withErrors(['email' => 'Akun ini sedang dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.'])->onlyInput('email');
        }

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->dashboardRouteName()));
    }

    public function registerForm()
    {
        return view('auth.register', [
            'students' => StudentRegistry::with('studyProgram')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function register(Request $request, MailConfiguration $mailConfiguration)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'student_registry_id' => [
                'required',
                Rule::exists('student_registries', 'id')->whereNull('user_id'),
            ],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'student_registry_id.exists' => 'Nama mahasiswa ini sudah memiliki akun atau tidak tersedia. Silakan masuk atau gunakan lupa password.',
        ]);

        $user = DB::transaction(function () use ($data) {
            $registry = StudentRegistry::with('studyProgram')
                ->whereKey($data['student_registry_id'])
                ->whereNull('user_id')
                ->lockForUpdate()
                ->firstOrFail();
            $user = User::create([
                'name' => $registry->name,
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
                'faculty_id' => $registry->studyProgram->faculty_id,
                'study_program_id' => $registry->study_program_id,
                'is_active' => true,
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'study_program_id' => $registry->study_program_id,
                'nim' => $registry->nim,
                'cohort' => $registry->cohort,
                'graduation_year' => $registry->graduation_year,
                'diploma_number' => $registry->diploma_number,
                'academic_title' => $registry->studyProgram->academic_title,
            ]);
            $registry->update(['user_id' => $user->id]);

            return $user;
        });

        Auth::login($user);

        if (! $mailConfiguration->isReady()) {
            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Akun berhasil dibuat, tetapi server email belum siap. Hubungi admin atau gunakan tombol kirim ulang setelah konfigurasi email diperbaiki.',
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Email verifikasi gagal dikirim.', ['exception' => $exception]);

            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Akun berhasil dibuat, tetapi email verifikasi belum dapat dikirim. Gunakan tombol kirim ulang atau hubungi admin.',
            ]);
        }

        return redirect()->route('verification.notice')->with('status', 'Tautan verifikasi telah dikirim ke email Anda.');
    }

    public function verificationNotice()
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->route($request->user()->dashboardRouteName())->with('success', 'Email berhasil diverifikasi.');
    }

    public function resendVerification(Request $request, MailConfiguration $mailConfiguration)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        if (! $mailConfiguration->isReady()) {
            return back()->withErrors([
                'email' => 'Server email belum siap. Hubungi admin agar konfigurasi SMTP diperiksa.',
            ]);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Pengiriman ulang email verifikasi gagal.', ['exception' => $exception]);

            return back()->withErrors([
                'email' => 'Email verifikasi belum dapat dikirim. Periksa alamat email, lalu coba kembali atau hubungi admin.',
            ]);
        }

        return back()->with('status', 'Tautan verifikasi baru telah dikirim.');
    }

    public function updateUnverifiedEmail(Request $request, MailConfiguration $mailConfiguration)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $request->user()->forceFill([
            'email' => $data['email'],
            'email_verified_at' => null,
        ])->save();

        if (! $mailConfiguration->isReady()) {
            return back()->withErrors(['email' => 'Alamat email sudah diperbarui, tetapi server email belum siap. Hubungi admin.']);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Email verifikasi gagal dikirim setelah alamat diubah.', ['exception' => $exception]);

            return back()->withErrors(['email' => 'Alamat email sudah diperbarui, tetapi tautan verifikasi belum dapat dikirim. Coba kembali atau hubungi admin.']);
        }

        return back()->with('status', 'Alamat email diperbarui dan tautan verifikasi baru telah dikirim.');
    }

    public function forgotForm()
    {
        return view('auth.forgot-password');
    }

    public function forgot(Request $request, MailConfiguration $mailConfiguration)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $request->validate(['email' => ['required', 'email']]);

        if (! $mailConfiguration->isReady()) {
            return back()->withErrors([
                'email' => 'Server email belum siap. Hubungi admin agar konfigurasi SMTP diperiksa.',
            ])->onlyInput('email');
        }

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            Log::error('Email reset password gagal dikirim.', ['exception' => $exception]);

            return back()->withErrors([
                'email' => 'Tautan reset belum dapat dikirim. Coba kembali atau hubungi admin.',
            ])->onlyInput('email');
        }

        return back()->with('status', 'Jika email terdaftar, tautan reset akan dikirim.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password berhasil diperbarui. Silakan masuk kembali.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
