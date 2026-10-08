<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
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
}
