<?php

namespace App\Http\Controllers;
use App\Models\Attack;

use Illuminate\Http\Request;

class AttackController extends Controller
{
    //
    public function create(Request $request){
        $attack = new Attack();
        $attack->valid = $request->valid;
        $attack->skill_id = $request->skill_id;
        $attack->game_id = $request->game_id;
        if($request->opportunity_name) $attack->opportunity_name = $request->opportunity_name;
        if($request->part_name) $attack->part_name = $request->part_name;
        $attack->save();
    }

    // いらないかも
    public function getList(Request $request){
        
    }
}
