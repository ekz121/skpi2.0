<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionDecision extends Model
{
    protected $guarded = [];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
