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
                "name" => "居着いたところ飛び込み面",
                "part_name" => "面",
                "opportunity_name" => "居付き",
                "description" => "自分から打ち間に入って狙う飛び込み面、片手面など"
            ],
            [
                "name" => "入り際飛び込み面",
                "part_name" => "面",
                "opportunity_name" => "入り際",
                "description" => "相手が打ち間に入ってきた時を狙った飛び込み面、片手面など"
            ],
            [
                "name" => "避けさせて面", 
                "part_name" => "面", 
                "opportunity_name" => "避けたところ",
                "description" => "自分から打ち間に入って打つ裏面、担ぎ面、小手フェイント面、胴フェイント面など"
            ],
            [
                "name" => "二段技面", 
                "part_name" => "面", 
                "opportunity_name" => "二段技",
                "description" => "小手面、面々、突き面など"
            ],
            [
                "name" => "出鼻面", 
                "part_name" => "面", 
                "opportunity_name" => "出鼻",
                "description" => "相手の出頭を狙った飛び込み面"
            ],
            [
                "name" => "面応じ系面", 
                "part_name" => "面", 
                "opportunity_name" => "応じ技",
                "description" => "面返し面、面抜き面など"
            ],
            [
                "name" => "小手応じ系面", 
                "part_name" => "面", 
                "opportunity_name" => "応じ技",
                "description" => "小手返し面、小手すりあげ面、小手抜き面、相小手面、小手打ち落とし面など"
            ],
            [
                "name" => "突き応じ系面", 
                "part_name" => "面", 
                "opportunity_name" => "応じ技",
                "description" => "突き返し面、突き抜き面など"
            ],
            [
                "name" => "引き面", 
                "part_name" => "面", 
                "opportunity_name" => "鍔迫り合い",
                "description" => "鍔迫り合いからの引面"
            ],
            [
                "name" => "近間面", 
                "part_name" => "面", 
                "opportunity_name" => "近間",
                "description" => "お互いに避けながら間合いを詰めるなどして、近い間合いができた時を狙った面"
            ],
            [
                "name" => "後打ちの面", 
                "part_name" => "面", 
                "opportunity_name" => "後打ち",
                "description" => "どちらかの選手が技を出した後の機会を狙った面"
            ],
            [
                "name" => "攻め込んで小手", 
                "part_name" => "小手", 
                "opportunity_name" => "避けたところ",
                "description" => "自分から打ち間に入ってうつ、飛び込み小手、面小手、かつぎ小手、払い小手、片手小手など"
            ],
            [
                "name" => "入り際小手", 
                "part_name" => "小手", 
                "opportunity_name" => "入り際",
                "description" => "相手が打ち間に入ってきた時を狙った飛び込み小手、面小手、片手小手など"
            ],
            [
                "name" => "出鼻小手", 
                "part_name" => "小手",
                "opportunity_name" => "出鼻",
                "description" => "相手の出頭を狙った小手、片手小手など"
            ],
            [
                "name" => "面応じ系小手", 
                "part_name" => "小手", 
                "opportunity_name" => "応じ技",
                "description" => "面返し小手、面抜き小手など"
            ],
            [
                "name" => "小手応じ系小手", 
                "part_name" => "小手", 
                "opportunity_name" => "応じ技",
                "description" => "小手返し小手、小手抜き小手、小手打ち落とし小手など"
            ],
            [
                "name" => "突き応じ系小手", 
                "part_name" => "小手", 
                "opportunity_name" => "応じ技",
                "description" => "突き抜き小手など"
            ],
            [
                "name" => "後打ちの小手", 
                "part_name" => "小手", 
                "opportunity_name" => "後打ち",
                "description" => "どちらかの選手が技を出した後の機会を狙った小手"
            ],
            [
                "name" => "引き小手", 
                "part_name" => "小手", 
                "opportunity_name" => "鍔迫り合い",
                "description" => "鍔迫り合いからの引き小手"
            ],
            [
                "name" => "近間小手", 
                "part_name" => "小手", 
                "opportunity_name" => "近間",
                "description" => "お互いに避けながら間合いを詰めるなどして、近い間合いができた時を狙った小手"
            ],
            [
                "name" => "避けさせて胴", 
                "part_name" => "胴",
                "opportunity_name" => "避けたところ",
                "description" => "相手が避けるところを狙った胴"
            ],
            [
                "name" => "応じ系胴", 
                "part_name" => "胴", 
                "opportunity_name" => "応じ技",
                "description" => "面返し胴、面抜き胴など"
            ],
            [
                "name" => "後打ちの胴", 
                "part_name" => "胴", 
                "opportunity_name" => "後打ち",
                "description" => "どちらかの選手が技を出した後の機会を狙った胴"
            ],
            [
                "name" => "引き胴", 
                "part_name" => "胴",
                "opportunity_name" => "鍔迫り合い",
                "description" => "鍔迫り合いからの引き胴"
            ],
            [
                "name" => "近間胴", 
                "part_name" => "胴", 
                "opportunity_name" => "近間",
                "description" => "お互いに避けながら間合いを詰めるなどして、近い間合いができた時を狙った胴"
            ],
            [
                "name" => "避けさせて逆胴",
                "part_name" => "胴", 
                "opportunity_name" => "避けたところ",
                "description" => "相手が避けるところを狙った逆胴"
            ],
            [
                "name" => "応じ系逆胴", 
                "part_name" => "胴", 
                "opportunity_name" => "応じ技",
                "description" => "抜き逆胴、面返し逆胴など"
            ],
            [
                "name" => "後打ちの逆胴", 
                "part_name" => "胴", 
                "opportunity_name" => "後打ち",
                "description" => "どちらかの選手が技を出した後の機会を狙った逆胴"
            ],
            [
                "name" => "引き逆胴", 
                "part_name" => "胴", 
                "opportunity_name" => "鍔迫り合い",
                "description" => "鍔迫り合いからの引き逆胴"
            ],
            [
                "name" => "近間逆胴", 
                "part_name" => "胴", 
                "opportunity_name" => "近間",
                "description" => "お互いに避けながら間合いを詰めるなどして、近い間合いができた時を狙った逆胴"
            ],
            [
                "name" => "突き", 
                "part_name" => "突き", 
                "opportunity_name" => "居付き",
                "description" => "両手突き、片手突き"
            ],
            [
                "name" => "後打ちの突き", 
                "part_name" => "突き", 
                "opportunity_name" => "後打ち",
                "description" => "どちらかの選手が技を出した後の機会を狙った逆胴"
            ],
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
