<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attack extends Model
{
    use HasFactory;

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
