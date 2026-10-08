<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityRule extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_mandatory' => 'boolean', 'is_active' => 'boolean'];
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function label(): string
    {
        return collect([$this->activity_type, $this->level, $this->achievement, $this->duration_label])
            ->filter()->join(' · ');
    }
}
