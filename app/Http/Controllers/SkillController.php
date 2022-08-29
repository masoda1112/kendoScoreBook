<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use App\Models\Skill;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            ["name" => "フェイント系面", "part_name" => "面", "opportunity_name" => "避けたところ"],
            ["name" => "二段技面", "part_name" => "面", "opportunity_name" => "二段技"],
            ["name" => "出鼻面", "part_name" => "面", "opportunity_name" => "出鼻"],
            ["name" => "後打ちの面", "part_name" => "面", "opportunity_name" => "後打ち"],
            ["name" => "面応じ系面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "小手応じ系面", "part_name" => "面", "opportunity_name" => "応じ技"],
            ["name" => "引き面", "part_name" => "面", "opportunity_name" => "鍔迫り合い"],
            ["name" => "飛び込み小手", "part_name" => "小手", "opportunity_name" => "避けたところ"],
            ["name" => "フェイント系小手", "part_name" => "小手", "opportunity_name" => "二段技"],
            ["name" => "二段技小手", "part_name" => "小手", "opportunity_name" => "二段技"],
            ["name" => "出鼻小手", "part_name" => "小手", "opportunity_name" => "出鼻"],
            ["name" => "面応じ系小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "小手応じ系小手", "part_name" => "小手", "opportunity_name" => "応じ技"],
            ["name" => "後打ちの小手", "part_name" => "小手", "opportunity_name" => "後打ち"],
            ["name" => "引き小手", "part_name" => "小手", "opportunity_name" => "鍔迫り合い"],
            ["name" => "飛び込み胴", "part_name" => "胴", "opportunity_name" => "避けたところ"],
            ["name" => "フェイント系胴", "part_name" => "胴", "opportunity_name" => "避けたところ"],
            ["name" => "二段技胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "応じ系胴", "part_name" => "胴", "opportunity_name" => "応じ技"],
            ["name" => "後打ちの胴", "part_name" => "胴", "opportunity_name" => "後打ち"],
            ["name" => "引き胴", "part_name" => "胴", "opportunity_name" => "鍔迫り合い"],
            ["name" => "飛び込み逆胴", "part_name" => "胴", "opportunity_name" => "避けたところ"],
            ["name" => "フェイント系逆胴", "part_name" => "胴", "opportunity_name" => "避けたところ"],
            ["name" => "二段技逆胴", "part_name" => "胴", "opportunity_name" => "二段技"],
            ["name" => "応じ系逆胴", "part_name" => "胴", "opportunity_name" => "応じ技"],
            ["name" => "後打ちの逆胴", "part_name" => "胴", "opportunity_name" => "後打ち"],
            ["name" => "引き逆胴", "part_name" => "胴", "opportunity_name" => "鍔迫り合い"],
            ["name" => "両手突き", "part_name" => "突き", "opportunity_name" => "居付き"],
            ["name" => "片手突き", "part_name" => "突き", "opportunity_name" => "居付き"],
            ["name" => "後打ちの突き", "part_name" => "突き", "opportunity_name" => "後打ち"],
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



    public function skill_index(){
        $skills = Skill::all();
        $skillList = ["id" => 0, "name" => "選択してください"];
        foreach($skills as $skill){
            array_push($skillList, array("id" => $skill->id, "name" => $skill->name));
        }
        return response()->json($skillList, Response::HTTP_OK);
    }

    public function show($skillId){
        return Skill::find($skillId);
    }
}
