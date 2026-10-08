<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['birthdate' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function isComplete(): bool
    {
        return filled($this->birthplace)
            && filled($this->birthdate)
            && filled($this->graduation_year)
            && filled($this->diploma_number)
            && filled($this->academic_title);
    }
}
