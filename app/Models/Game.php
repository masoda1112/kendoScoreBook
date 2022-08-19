<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public function attacks()
    {
        return $this->hasMany(Attacks::class);
    }

    public function fouls()
    {
        return $this->hasMany(Fouls::class);
    }
    
}
