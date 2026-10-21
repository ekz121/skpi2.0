<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'student',
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'faculty_id',
        'study_program_id',
        'is_active',
        'deactivated_at',
        'deactivated_by',
        'email_verified_at',
        'password',
    ];

    public const ROLES = [
        'student' => 'Mahasiswa',
        'program_verifier' => 'Verifikator Prodi',
        'admin' => 'Admin',
        'leader' => 'Pimpinan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function skpiRequests()
    {
        return $this->hasMany(SkpiRequest::class);
    }

    public function registry()
    {
        return $this->hasOne(StudentRegistry::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function deactivatedBy()
    {
        return $this->belongsTo(self::class, 'deactivated_by');
    }

    public function verifiedSubmissions()
    {
        return $this->hasMany(Submission::class, 'verified_by');
    }

    public function checkedSubmissions()
    {
        return $this->hasMany(Submission::class, 'admin_checked_by');
    }

    public function submissionDecisions()
    {
        return $this->hasMany(SubmissionDecision::class, 'admin_id');
    }

    public function reviewedSkpiRequests()
    {
        return $this->hasMany(SkpiRequest::class, 'reviewed_by');
    }

    public function skpiWithdrawals()
    {
        return $this->hasMany(SkpiWithdrawal::class, 'withdrawn_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'student' => 'student.dashboard',
            default => 'staff.dashboard',
        };
    }

    public function hasRelatedAcademicData(): bool
    {
        return $this->submissions()->exists()
            || $this->skpiRequests()->exists()
            || $this->verifiedSubmissions()->exists()
            || $this->checkedSubmissions()->exists()
            || $this->submissionDecisions()->exists()
            || $this->reviewedSkpiRequests()->exists()
            || $this->skpiWithdrawals()->exists();
    }

    public function approvedPoints(): int
    {
        return (int) $this->submissions()->where('status', 'approved')->sum('approved_points');
    }
}
