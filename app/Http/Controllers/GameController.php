<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    //
    public function create(Request $request){
        

    }

    public function index(User $user){
        $games = $user->games();
        $gameList = [];
        foreach ($games as $game) {
            array_push($gameList,$game);
        }

        return $gameList;
    }

    public function show(Request $request){

    }

    // 円グラフ用のデータ整形
    private function calculateSkillRate(Game $game){
        $attacks = $game->attacks();
        $skills = [];
        $skill = SkillController::show($attack->skill_id);
        foreach ($attacks as $attack) {
            array_push($skills, $skill->name);
        }

        return $skills;
    }
}
