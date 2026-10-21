<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = ['code', 'name'];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
