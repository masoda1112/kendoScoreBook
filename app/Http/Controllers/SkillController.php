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

    public function defaultCreate(){
        $skillArray = array(
            ["name" => "飛び込み面", "part_name" => "面", "opportunity_name" => "居付き"],
            ["name" => "出鼻面", "part_name" => "面", "opportunity_name" => "出鼻"],
            ["name" => "小手から面", "part_name" => "面", "opportunity_name" => "二段技"],
            ["name" => "面から面", "part_name" => "面", "opportunity_name" => "二段技"],
            ["name" => "面から裏面", "part_name" => "面", "opportunity_name" => "二段技"],
            ["name" => "面返し面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "小手返し面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "面抜き面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "小手抜き面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "小手すりあげ面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "飛び込み小手", "part_name" => "小手", "opportunity_name" => "避けたところ"],
            ["name" => "面から小手", "part_name" => "小手", "opportunity_name" => "二段技"],
            ["name" => "小手から小手", "part_name" => "小手", "opportunity_name" => "二段技"],
            ["name" => "出鼻小手", "part_name" => "小手", "opportunity_name" => "出鼻"],
            ["name" => "面返し小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "面抜き小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "小手返し小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "小手抜き小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "飛び込み胴", "part_name" => "胴", "opportunity_name" => "避けたところ"],
            ["name" => "面から胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "面から逆胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "小手から胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "小手から逆胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "抜き胴", "part_name" => "胴", "opportunity_name" => "応じ技"],
            ["name" => "抜き逆胴", "part_name" => "胴", "opportunity_name" => "応じ技"],
            ["name" => "返し胴", "part_name" => "胴", "opportunity_name" => "応じ技"],
            ["name" => "両手突き", "part_name" => "突き", "opportunity_name" => "居付き"],
            ["name" => "片手突き", "part_name" => "突き", "opportunity_name" => "居付き"],
            ["name" => "後打ちの面", "part_name" => "面", "opportunity_name" => "後打ち"],
            ["name" => "後打ちの小手", "part_name" => "小手", "opportunity_name" => "後打ち"],
            ["name" => "後打ちの胴", "part_name" => "胴", "opportunity_name" => "後打ち"],
            ["name" => "後打ちの突き", "part_name" => "突き", "opportunity_name" => "後打ち"],
            ["name" => "引き面", "part_name" => "面", "opportunity_name" => "鍔迫り合い"],
            ["name" => "引き小手", "part_name" => "小手", "opportunity_name" => "鍔迫り合い"],
            ["name" => "引き胴", "part_name" => "胴", "opportunity_name" => "鍔迫り合い"],
        );

        foreach($skillArray as $val){
            $this->skills_create($val); 
        };
    }


    function skills_create ($value){
        Skill::create([
            "name" => $value["name"],
            "part_name" => $value["part_name"],
            "opportunity_name" => $value["opportunity_name"]
        ]);
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
