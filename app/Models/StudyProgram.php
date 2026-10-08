<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = ['code', 'national_code', 'name', 'academic_title', 'accreditation', 'learning_outcomes'];

    protected function casts(): array
    {
        return ['learning_outcomes' => 'array'];
    }

    public function students()
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function registries()
    {
        return $this->hasMany(StudentRegistry::class);
    }
}
