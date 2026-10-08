<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRegistry extends Model
{
    protected $guarded = [];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
