<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'student.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::get('/lupa-password', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'forgot'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/verifikasi-email', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/verifikasi-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/verifikasi-email/kirim-ulang', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')->name('verification.send');
    Route::get('/bukti/{submission}', [SubmissionController::class, 'downloadEvidence'])->name('evidence.download');
    Route::delete('/notifikasi', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifikasi/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

Route::prefix('mahasiswa')->name('student.')->middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil', [StudentController::class, 'profile'])->name('profile');
    Route::put('/profil', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::get('/pengajuan', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/pengajuan/baru', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/pengajuan', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/pengajuan/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/pengajuan/{submission}/ubah', [SubmissionController::class, 'edit'])->name('submissions.edit');
    Route::put('/pengajuan/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
    Route::get('/rekap-poin', [StudentController::class, 'recap'])->name('recap');
    Route::get('/panduan', [StudentController::class, 'guide'])->name('guide');
    Route::get('/skpi', [StudentController::class, 'skpi'])->name('skpi');
    Route::post('/skpi', [StudentController::class, 'requestSkpi'])->name('skpi.request');
    Route::get('/skpi/{skpiRequest}/unduh/{format}', [StudentController::class, 'downloadSkpi'])->whereIn('format', ['pdf', 'docx'])->name('skpi.download');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/sertifikat', [AdminController::class, 'submissions'])->name('submissions.index');
    Route::get('/sertifikat/baru', [AdminController::class, 'createSubmission'])->name('submissions.create');
    Route::post('/sertifikat', [AdminController::class, 'storeSubmission'])->name('submissions.store');
    Route::get('/sertifikat/{submission}', [AdminController::class, 'showSubmission'])->name('submissions.show');
    Route::get('/sertifikat/{submission}/ubah', [AdminController::class, 'editSubmission'])->name('submissions.edit');
    Route::put('/sertifikat/{submission}', [AdminController::class, 'updateSubmission'])->name('submissions.update');
    Route::put('/sertifikat/{submission}/cek', [AdminController::class, 'checkSubmission'])->name('submissions.check');
    Route::delete('/sertifikat/{submission}', [AdminController::class, 'destroySubmission'])->name('submissions.destroy');
    Route::get('/mahasiswa', [AdminController::class, 'students'])->name('students');
    Route::get('/skpi', [AdminController::class, 'skpiRequests'])->name('skpi.index');
    Route::post('/skpi/aksi-massal', [AdminController::class, 'bulkSkpi'])->name('skpi.bulk');
    Route::get('/skpi/{skpiRequest}/unduh/{format}', [AdminController::class, 'downloadSkpi'])->whereIn('format', ['pdf', 'docx'])->name('skpi.download');
    Route::get('/skpi/{skpiRequest}', [AdminController::class, 'showSkpi'])->name('skpi.show');
    Route::put('/skpi/{skpiRequest}', [AdminController::class, 'updateSkpi'])->name('skpi.update');
});
