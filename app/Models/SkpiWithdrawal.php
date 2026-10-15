<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkpiWithdrawal extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['withdrawn_at' => 'datetime'];
    }

    public function skpiRequest()
    {
        return $this->belongsTo(SkpiRequest::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }
}
