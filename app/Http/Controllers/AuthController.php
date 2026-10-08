<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\StudentRegistry;
use App\Models\User;
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($request->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard'));
    }

    public function registerForm()
    {
        return view('auth.register', [
            'students' => StudentRegistry::with('studyProgram')
                ->whereNull('user_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'student_registry_id' => [
                'required',
                Rule::exists('student_registries', 'id')->whereNull('user_id'),
            ],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', PasswordRule::min(8)->letters()->numbers()],
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

        if (! $this->mailIsConfigured()) {
            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Server email belum dikonfigurasi. Isi MAIL_USERNAME dan MAIL_PASSWORD Gmail pada file .env, lalu kirim ulang tautan.',
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Email verifikasi gagal dikirim.', ['exception' => $exception]);

            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Email verifikasi gagal dikirim. Periksa konfigurasi Gmail SMTP lalu coba kirim ulang.',
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

        return redirect()->route('student.dashboard')->with('success', 'Email berhasil diverifikasi.');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('student.dashboard');
        }

        if (! $this->mailIsConfigured()) {
            return back()->withErrors([
                'email' => 'Server email belum dikonfigurasi. Isi MAIL_USERNAME dan MAIL_PASSWORD Gmail pada file .env.',
            ]);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::error('Pengiriman ulang email verifikasi gagal.', ['exception' => $exception]);

            return back()->withErrors([
                'email' => 'Email verifikasi gagal dikirim. Periksa konfigurasi Gmail SMTP lalu coba lagi.',
            ]);
        }

        return back()->with('status', 'Tautan verifikasi baru telah dikirim.');
    }

    public function forgotForm()
    {
        return view('auth.forgot-password');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        if (! $this->mailIsConfigured()) {
            return back()->withErrors([
                'email' => 'Server email belum dikonfigurasi. Isi MAIL_USERNAME dan MAIL_PASSWORD Gmail pada file .env.',
            ])->onlyInput('email');
        }

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            Log::error('Email reset password gagal dikirim.', ['exception' => $exception]);

            return back()->withErrors([
                'email' => 'Tautan reset gagal dikirim. Periksa konfigurasi Gmail SMTP lalu coba lagi.',
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

    private function mailIsConfigured(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if ($mailer !== 'smtp') {
            return true;
        }

        return filled(config('mail.mailers.smtp.host'))
            && filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.mailers.smtp.password'))
            && filled(config('mail.from.address'));
    }
}
