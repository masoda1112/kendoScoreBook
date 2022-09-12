<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use App\Models\Skill;
use App\Models\Foul;
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
            [
                "name" => "飛び込み面",
                "part_name" => "面",
            ],
            [
                "name" => "出鼻面",
                "part_name" => "面",
            ],
            [
                "name" => "フェイント系面",
                "part_name" => "面",
            ],
            [
                "name" => "面応じ面",
                "part_name" => "面",
            ],
            [
                "name" => "小手応じ面",
                "part_name" => "面",
            ],
            [
                "name" => "胴応じ面",
                "part_name" => "面",
            ],
            [
                "name" => "突き応じ面",
                "part_name" => "面",
            ],
            [
                "name" => "連続技の面",
                "part_name" => "面",
            ],
            [
                "name" => "引面",
                "part_name" => "面",
            ],
            [
                "name" => "飛び込み小手",
                "part_name" => "小手",
            ],
            [
                "name" => "出鼻小手",
                "part_name" => "小手",
            ],
            [
                "name" => "フェイント系小手",
                "part_name" => "小手",
            ],
            [
                "name" => "連続技の小手",
                "part_name" => "小手",
            ],
            [
                "name" => "引小手",
                "part_name" => "小手",
            ],
            [
                "name" => "面応じ小手",
                "part_name" => "小手",
            ],
            [
                "name" => "小手応じ小手",
                "part_name" => "小手",
            ],
            [
                "name" => "胴応じ小手",
                "part_name" => "小手",
            ],
            [
                "name" => "飛び込み胴",
                "part_name" => "胴",
            ],
            [
                "name" => "フェイント系胴",
                "part_name" => "胴",
            ],
            [
                "name" => "連続技の胴",
                "part_name" => "胴",
            ],
            [
                "name" => "面応じ胴",
                "part_name" => "胴",
            ],
            [
                "name" => "突き応じ胴",
                "part_name" => "胴",
            ],
            [
                "name" => "引き胴",
                "part_name" => "胴",
            ],
            [
                "name" => "飛び込み逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "フェイント系逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "連続技の逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "面応じ逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "突き応じ逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "引き逆胴",
                "part_name" => "胴",
            ],
            [
                "name" => "両手突き",
                "part_name" => "突き",
            ],
            [
                "name" => "片手突き",
                "part_name" => "突き",
            ],
        );

        foreach($skillArray as $val){
            $this->skills_create($val); 
        };
    }


    function skills_create ($value){
        Skill::create([
            "name" => $value["name"],
            "part_name" => $value["part_name"]
        ]);
    }



    public function skill_index(){
        $skills = Skill::all();
        $skillList = [["id" => 0, "name" => "選択してください"]];

        foreach($skills as $skill){
            array_push($skillList, array("id" => $skill->id, "name" => $skill->name));
        }

        $response = [
            "skills" => $skillList,
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function show($skillId){
        return Skill::find($skillId);
    }
}
