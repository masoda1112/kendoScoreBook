<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Game;
use App\Models\Attack;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AttackController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @var Firebase
    */
    private $firebase;

    /**
     * コンストラクタインジェクションで $firebase を用意します
     * @param Firebase $firebase
    */
    
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->where('password', $request->password)->first();
        if(!$user){
            return response()->json('email又はpasswordが無効です', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $tokenResult = $user->createToken('Personal Access Token');
        $user->access_token = $tokenResult->accessToken;
        $user->save();

        $json = [
            'id' => $user->id,
            'user_name' => $user->name,
            'access_token' => $user->access_token,
            'token_type' => 'Bearer',
        ];

        return response()->json($json, Response::HTTP_OK);
    }

    public function register(Request $request)
    {
        /** @var Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->create($request);
        $json = [
            'access_token' => $user->access_token,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ];

        return response()->json($json, Response::HTTP_OK);
    }

    public function logout (Request $request) {
        auth('sanctum')->user()->tokens()->delete();
        return response(['message' => 'You have been successfully logged out.'], 200);
    }

    public function index(Request $request){
        $user = $request->user();
        $response = $this->buildResponseData($user->games, false);
        return response()->json($response, Response::HTTP_OK);
    }

    public function averageData(Request $request){
        $all_games = Game::all();
        $response = $this->buildResponseData($all_games, true);
        return response()->json($response, Response::HTTP_OK);
    }

    public function getGameIndex(Request $request){
        $user = $request->user();
        $game_list = [];

        foreach($user->games as $game){
            $attack_list = $this->getAttackList($game, true);
            array_push(
                $game_list,
                array(
                    "id" => $game->id,
                    "competitor_name" => $game->competitor_name,
                    "result_id" => $game->result_id,
                    "competitor_valid_attack" => $attack_list["competitor"],
                    "valid_attack" => $attack_list["self"],
                    "date" => date('Y/m/d', strtotime($game->updated_at))
                )
            );
        }

        return response()->json([
            'user' => $user->name,
            'games'=> $game_list
        ], Response::HTTP_OK);
    }

    public function getGame(Request $request, $user_name, $game_id){
        $user = $request->user();
        // game_idはパスから取る
        // getAttackList使うのが良さげ
        $valid_attack_list = [];
        $competitor_valid_attack_list = [];
        $competitor_attack_list = [];
        $attack_list = [];
        $foul_list = [];
        $competitor_foul_list = [];
        $time = 0;
        $competitor_name = "";
        $date = "";

        foreach($user->games as $game){
            if($game->id == $game_id){
                $competitor_name = $game->competitor_name;
                $date = $game->updated_at;
                $time = $game->seconds;
                foreach($game->fouls as $foul){
                    if($foul->competitor){
                        $foul_list[] = $foul->name;
                    }else{
                        $competitor_foul_list[] = $foul->name;
                    }
                }
                foreach($game->attacks as $attack){
                    if($attack->competitor){
                        if($attack->valid){
                            array_push($competitor_valid_attack_list, $attack->skill->part_name);
                            $competitor_attack_list[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $competitor_attack_list, true, false);
                        }
                    }else{
                        if($attack->valid){
                            array_push($valid_attack_list, $attack->skill->part_name);
                            $attack_list[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $attack_list, true, false);
                        }else if($attack->defeat){
                            $attack_list[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $attack_list, false, true);
                        }else{
                            $attack_list[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $attack_list, false, false);
                        }
                    }
                }
            }
        }

        array_multisort($attack_list, SORT_DESC);

        $response_data = [
            "date" => date('Y/m/d', strtotime($game->updated_at)),
            "id" => $game_id,
            "competitor_name" => $competitor_name,
            "valid_attack_list" => $valid_attack_list,
            "competitor_valid_attack_list" => $competitor_valid_attack_list,
            "attack_list" => $attack_list,
            "competitor_attack_list" => $competitor_attack_list,
            "foul_list" => $foul_list,
            "competitor_foul_list" => $competitor_foul_list,
            "time" => $time
        ];

        return response()->json($response_data, Response::HTTP_OK);
    }

    public function addGame(Request $request){
        $user = $request->user();

        // ここに問題あり
        $game = $user->games()->create([
            'competitor_name' =>  $request->competitor_name,
            'result_id' => $request->result_id,
            'seconds' => $request->time,
        ]);
        
        // attack配列作成
        $validAttacks = $this->createAttackLoop($request->valid_attacks, false, true, false);
        $competitorValidAttacks = $this->createAttackLoop($request->competitor_valid_attacks, true, true, false);
        $attacks = $this->createAttackLoop($request->attacks, false, false, false);
        $defeatAttacks = $this->createAttackLoop($request->defeat_attacks, false, false, true);
        $totalAttacksArray = array_merge($validAttacks, $competitorValidAttacks, $attacks, $defeatAttacks);

        // foul配列作成
        $fouls = $this->createFoulLoop($request->fouls, false);
        $competitorFouls = $this->createFoulLoop($request->competitor_fouls, true);
        $totalFoulArray = array_merge($fouls, $competitorFouls);

        //attack,foul作成
        $game->attacks()->createMany($totalAttacksArray);
        $game->fouls()->createMany($totalFoulArray);
        
        return response()->json($game ,Response::HTTP_OK);
    }

    private function getAttackList($game, $valid){

        $competitorAttacks = [];
        $selfAttacks = [];
        foreach($game->attacks as $attack){
            if($attack->valid == $valid){
                if($attack->competitor){
                    array_push($competitorAttacks, $attack->skill->part_name);
                }else{
                    array_push($selfAttacks, $attack->skill->part_name);
                }
            }
        }

        return array("competitor" => $competitorAttacks, "self" => $selfAttacks);
    }

    private function createAttackLoop($array, $competitor, $valid, $defeat){
        $attacks = [];
            foreach($array as $attack){
                if($attack != null){
                    $attack_array = array(
                        "skill_id" => $attack,
                        "competitor" => $competitor,
                        "valid" => $valid,
                        "defeat" => $defeat
                    );
                    array_push($attacks, $attack_array);
                }
            }
        return $attacks;
    }

    private function createFoulLoop($array, $competitor){
        $fouls = [];
        $foul_option_list = ["選択してください", "場外反則", "竹刀落とし", "時間空費", "その他"];
        foreach($array as $foul){
            if($foul != null){
                $foul_array = array(
                    "name" => $foul_option_list[$foul],
                    "competitor" => $competitor,
                );
                array_push($fouls, $foul_array);
            }
        }

        return $fouls;
    }

    private function create(Request $request){
        // ここでfirebaseに問い合わせる
        $uid = $this->confirmUid();
        $user = User::create([
            'name' =>  $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'firebase_uid' => $uid
        ]);
        $tokenResult = $user->createToken('Personal Access Token');
        $user->access_token = $tokenResult->accessToken;
        $user->save();
        
        return $user;
    }

    private function confirmUid(){
        $header = getallheaders();
        $authorization = $header['Authorization'];
        $idToken = ltrim(ltrim($authorization, 'Bearer'));
        $uid = $this->getUidByToken($authorization);
        return $uid;
    }

    private function getUidByToken($idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
        } catch (InvalidToken $e) {
            echo 'The token is invalid: ' . $e->getMessage();
        } catch (\InvalidArgumentException $e) {
            echo 'The token could not be parsed: ' . $e->getMessage();
        }

        $uid = $verifiedIdToken->claims()->get('sub');

        return $uid;
    }

    private function buildResponseData($games, $average){
        $winCount = 0;
        $loseCount = 0;
        $total = 0;
        $gameTime = 0;
        $attackCount = 0;
        $validAttackCount = 0;
        $circle_graph_rate = [];
        $bar_graph_rate = [];
        $competitor_circle_graph_rate = [];

        foreach ($games as $game) {
            $total += 1;
            $gameTime += $game->seconds;
            if($game->result_id == 1){
                $winCount += 1;
            }else if($game->result_id == 2){
                $loseCount += 1;
            }

            foreach($game->attacks as $attack){
                if($attack->competitor){
                    if($attack->valid){
                        if(!$average)$competitor_circle_graph_rate[$attack->skill->name] = $this->addCircleGraphRate($attack->skill->name, $competitor_circle_graph_rate);
                    }
                }else{
                    $attackCount += 1;
                    if(!$average)$circle_graph_rate[$attack->skill->name] = $this->addCircleGraphRate($attack->skill->name, $circle_graph_rate);

                    if($attack->valid){
                        $validAttackCount += 1;
                        if(!$average)$bar_graph_rate[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $bar_graph_rate, true, false);
                    }else if($attack->defeat){
                        if(!$average)$bar_graph_rate[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $bar_graph_rate, false, true);
                    }else{
                        if(!$average)$bar_graph_rate[$attack->skill->name] = $this->addBarGraphRate($attack->skill->name, $bar_graph_rate, false, false);
                    }
                }
            }
        }

        $circle_graph_rate = $this->otherBuild($circle_graph_rate);
        $competitor_circle_graph_rate = $this->otherBuild($competitor_circle_graph_rate);
        array_multisort($bar_graph_rate, SORT_DESC);

        $response_array = [];
        if($average){
            $response_array = array(
                "validAttackCount" => $validAttackCount,
                "attackCount" => $attackCount,
                "totalGameTime" => round($gameTime / 60, 2),
            );
        }else{
            $response_array = array(
                "winGameCount" => $winCount,
                "loseGameCount" => $loseCount,
                "totalGameCount" => $total,
                "validAttackCount" => $validAttackCount,
                "attackCount" => $attackCount,
                "totalGameTime" => round($gameTime / 60, 2),
                "circleGraphRate" => $circle_graph_rate,
                "barGraphRate" => $bar_graph_rate,
                "competitorCircleGraphRate" => $competitor_circle_graph_rate,
            );
        }

        return $response_array;
    }


    private function addCircleGraphRate ($skill_name, $array){
        if(array_key_exists($skill_name, $array)){
            return $array[$skill_name] += 1;
        }else{
            return 1;
        }
    }

    private function addBarGraphRate($skill_name, $array, $valid, $defeat){
        if(array_key_exists($skill_name, $array)){
            if($valid){
                return [
                    "無効打"=> $array[$skill_name]["無効打"], 
                    "有効打" => $array[$skill_name]["有効打"] + 1,
                    "被有効打"=> $array[$skill_name]["被有効打"], 
                ];
            }else if($defeat){
                return [
                    "無効打"=> $array[$skill_name]["無効打"], 
                    "有効打" => $array[$skill_name]["有効打"],
                    "被有効打"=> $array[$skill_name]["被有効打"] + 1, 
                ];
            }else{
                return [
                    "無効打"=> $array[$skill_name]["無効打"] + 1, 
                    "有効打" => $array[$skill_name]["有効打"],
                    "被有効打"=> $array[$skill_name]["被有効打"], 
                ];
            }
        }else{
            if($valid){
                return ["無効打" => 0, "有効打" => 1, "被有効打" => 0];
            }else if($defeat){
                return ["無効打" => 0, "有効打" => 0, "被有効打" => 1];
            }else{
                return ["無効打" => 1, "有効打" => 0, "被有効打" => 0];
            }
        }
    }

    private function otherBuild($array){

        array_multisort($array, SORT_DESC);

        $array_other = array_slice($array, 10, count($array));
        $other = array_sum($array_other);

        $array = array_slice($array, 0, 9);
        $array["その他"] = $other;
        
        return $array;
    }

}
