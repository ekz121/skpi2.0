<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    public const CUSTOM_CATEGORY = 'Sertifikat Lain-lain';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'verified_at' => 'datetime',
            'admin_checked_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rule()
    {
        return $this->belongsTo(ActivityRule::class, 'activity_rule_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'admin_checked_by');
    }

    public function decisions()
    {
        return $this->hasMany(SubmissionDecision::class);
    }

    public function isCustomType(): bool
    {
        return $this->rule?->category === self::CUSTOM_CATEGORY;
    }

    public function categoryLabel(): string
    {
        return $this->rule?->category ?? 'Kategori tidak tersedia';
    }

    public function activityTypeLabel(): string
    {
        if ($this->isCustomType()) {
            return $this->custom_activity_type ?: 'Lain-lain';
        }

        return $this->rule?->label() ?: 'Jenis kegiatan tidak tersedia';
    }

    public function pointsLabel(): string
    {
        if ($this->isCustomType()) {
            return '-';
        }

        return (string) ($this->approved_points ?? $this->estimated_points ?? 0).' poin';
    }
}
