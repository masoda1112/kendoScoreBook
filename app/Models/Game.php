<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Attack;
use App\Models\Foul;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'competitor_name',
        'result_id',
        'tsubazeriai_seconds',
        'wakare_count',
        'foul_count',
        'seconds',
        'user_id',
    ];

    public function attacks()
    {
        return $this->hasMany(Attack::class);
    }

    public function fouls()
    {
        return $this->hasMany(Foul::class);
    }
    
}
