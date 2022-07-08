<?php

namespace App\Http\Controllers;
use App\Models\Skill;


use Illuminate\Http\Request;

class SkillController extends Controller
{
    //

    public function create(Request $request){
        $skill = new Skill();
        $skill->name = $request->name;
        if($request->part_name) $skill->part_name = $request->part_name;
        if($request->opportunity_name) $skill->opportunity_name = $request->opportunity_name;
        $skill->save();
        response()->json([$skill]);
    }

    public function index(){
        $skills = Skill::all();
        $skillList = [];
        foreach($skills as $skill){
            array_push($skillList, $skill->name);
        }
        return $skillList;
    }

    public function show($skillId){
        return Skill::find($skillId);
    }
}
