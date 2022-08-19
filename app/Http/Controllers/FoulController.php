<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FoulController extends Controller
{
    //
    public function create (){
        $foul = new Foul();
        $foul->foul_name = $request->foul_name;
        $foul->game_id = $request->game_id;
        $foul->compatitor = $request->compatitor;
        $foul->save();
    }
}
