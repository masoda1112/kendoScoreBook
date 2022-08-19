<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Game;
use Http\Controllers\SkillController;
use Http\Controllers\AttackController;

use Illuminate\Http\Request;

class GameController extends Controller
{
    //
    public function create(Request $request){
        $game = new Game();
        $game->competitor_name = $request->competitor_name;
        $game->result_id = $request->result_id;
        $game->seconds = $request->seconds;
        // attackを連続で作成&gameに登録
        $game->save();

        return $game;
    }

    public function index(User $user){
        $games = $user->games();
        $gameList = [];
        // gameの形によってはうまくいかない
        foreach ($games as $game) {
            array_push($gameList,$game);
        }

        return $gameList;
    }

    public function show($user, $game_id){
        $games = $user->games();
        $resGame = "";
        foreach ($games as $game) {
            if($game->id == $game_id) $resGame = $game;
        }

        $overView = $this->buildGameOverView($resGame);
        $skillRate = $this->calculateSkillRate($resGame);

        return array("overView" => $overView, "skillRate" => $skillRate);
    }

    private function buildGameOverView(Game $game){
        $resHash = array("competitor_name" => $game->competitor_name);
        $attacks = $game->attacks();
        foreach($attacks as $attack){
            if($attack->valid){
                if($attack->competitor){
                    if($resHash["competitor_attack"]){
                        $resHash["competitor_attack"] += Skill::find($attack->skill_id)->name;
                    }else{
                        $resHash += array("competitor_attack" => Skill::find($attack->skill_id)->name);
                    }
                }else{
                    if($resHash["attack"]){
                        $resHash["atatck"] += Skill::find($attack->skill_id)->name;
                    }else{
                        $resHash += array("attack" => Skill::find($attack->skill_id)->name);
                    }
                }
            }
        }
        return $resHash;
    }

    private function calculateSkillRate(Game $game){
        $attacks = $game->attacks();
        $skills = [];
        $skill = SkillController::show($attack->skill_id);
        foreach ($attacks as $attack) {
            if($skills[$skill->name]){
                $skills[$skill->name] += 1;
            }else{
                $skills += array($skill->name => 1);
            }
        }

        return $skills;
    }
}
