<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Skill;

class Attack extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'game_id',
        'tsubazeriai_seconds',
        'opportunity_name',
        'part_name',
        'competitor',
        'valid',
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
