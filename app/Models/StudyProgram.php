<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = ['faculty_id', 'code', 'national_code', 'name', 'academic_title', 'accreditation', 'learning_outcomes'];

    protected function casts(): array
    {
        return ['learning_outcomes' => 'array'];
    }

    public function students()
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function registries()
    {
        return $this->hasMany(StudentRegistry::class);
    }
}
