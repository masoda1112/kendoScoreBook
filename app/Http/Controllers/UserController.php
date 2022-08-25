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
        $response = $this->buildResponseData($user);
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
                        }
                    }else{
                        if($attack->valid){
                            array_push($valid_attack_list, $attack->skill->part_name);
                            if(array_key_exists($attack->skill->name, $attack_list)){
                                $attack_list[$attack->skill->name] = [
                                    "無効打"=> $attack_list[$attack->skill->name]["無効打"], 
                                    "有効打" => $attack_list[$attack->skill->name]["有効打"] + 1
                                ];
                            }else{
                                $attack_list[$attack->skill->name] = ["無効打" => 0, "有効打" => 1];
                            }
                        }else{
                            if(array_key_exists($attack->skill->name, $attack_list)){
                                $attack_list[$attack->skill->name] = [
                                    "無効打"=> $attack_list[$attack->skill->name]["無効打"] + 1 ,
                                    "有効打" => $attack_list[$attack->skill->name]["有効打"]
                                ];
                            }else{
                                $attack_list[$attack->skill->name] = ["無効打" => 1, "有効打" => 0];
                            }
                        }
                    }
                }
            }
        }

        $response_data = [
            "date" => date('Y/m/d', strtotime($game->updated_at)),
            "id" => $game_id,
            "competitor_name" => $competitor_name,
            "valid_attack_list" => $valid_attack_list,
            "competitor_valid_attack_list" => $competitor_valid_attack_list,
            "attack_list" => $attack_list,
            "foul_list" => $foul_list,
            "competitor_foul_list" => $competitor_foul_list,
            "time" => $time
        ];

        return response()->json($response_data, Response::HTTP_OK);
    }

    public function addGame(Request $request){
        // requestの形{competitor=>"", resultId=>"", time=> "", attacks => [{skill_id => ""}, {},{}] }
        $user = $request->user();

        // ここに問題あり
        $game = $user->games()->create([
            'competitor_name' =>  $request->competitor_name,
            'result_id' => $request->result_id,
            'seconds' => $request->time,
        ]);
        
        // attack配列作成
        $validAttacks = $this->createAttackLoop($request->valid_attacks, false, true);
        $competitorAttacks = $this->createAttackLoop($request->competitor_attacks, true, true);
        $attacks = $this->createAttackLoop($request->attacks, false, false);
        $totalAttacksArray = array_merge($validAttacks , $competitorAttacks, $attacks);

        // foul配列作成
        $fouls = $this->createFoulLoop($request->fouls, false);
        $competitorFouls = $this->createFoulLoop($request->competitor_fouls, true);
        $totalFoulArray = array_merge($fouls, $competitorFouls);

        //attack,foul作成
        $game->attacks()->createMany($totalAttacksArray);
        $game->fouls()->createMany($totalFoulArray);
        
        response()->json($game ,Response::HTTP_OK);
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

    private function createAttackLoop($array, $competitor, $valid){
        $attacks = [];
        foreach($array as $attack){
            // Attempt to read property "skill_id" on intだとさ
            if($attack != null){
                $attack_array = array(
                    "skill_id" => $attack,
                    "competitor" => $competitor,
                    "valid" => $valid,
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

    private function buildResponseData($user){
        // 完結：winRate,validAttackRate,attackCount
        // 補助：caluculateSkillRate,calculateValidSkillRate
        $winCount = 0;
        $total = 0;
        $gameTime = 0;
        $attackCount = 0;
        $validAttackCount = 0;
        $circle_graph_rate = [];
        $bar_graph_rate = [];
        $competitor_circle_graph_rate = [];

        foreach ($user->games as $game) {
            $total += 1;
            $gameTime += $game->seconds;
            if($game->result_id == 1) $winCount += 1;
            // $gameAttacks += $this->calculateAttacks($game);
            // ここでrate系の計算の分は繰り返し処理しておく
            foreach($game->attacks as $attack){
                if($attack->competitor){
                    Log::debug("competitor");
                    if(array_key_exists($attack->skill->name, $competitor_circle_graph_rate)){
                        $competitor_circle_graph_rate[$attack->skill->name] += 1;
                    }else{
                        $competitor_circle_graph_rate[$attack->skill->name] = 1;
                    }
                }else{
                    $attackCount += 1;
                    if(array_key_exists($attack->skill->name, $circle_graph_rate)){
                        $circle_graph_rate[$attack->skill->name] += 1;
                    }else{
                        $circle_graph_rate[$attack->skill->name] = 1;
                    }
                    
                    if($attack->valid){
                        Log::debug("valid");
                        $validAttackCount += 1;
                        if(array_key_exists($attack->skill->name, $bar_graph_rate)){
                            $bar_graph_rate[$attack->skill->name] = [
                                "無効打"=> $bar_graph_rate[$attack->skill->name]["無効打"], 
                                "有効打" => $bar_graph_rate[$attack->skill->name]["有効打"] + 1
                            ];
                        }else{
                            $bar_graph_rate[$attack->skill->name] = ["無効打" => 0, "有効打" => 1];
                        }
                    }else{
                        if(array_key_exists($attack->skill->name, $bar_graph_rate)){
                            $bar_graph_rate[$attack->skill->name] = [
                                "無効打"=> $bar_graph_rate[$attack->skill->name]["無効打"] + 1 ,
                                "有効打" => $bar_graph_rate[$attack->skill->name]["有効打"]
                            ];
                        }else{
                            $bar_graph_rate[$attack->skill->name] = ["無効打" => 1, "有効打" => 0];
                        }
                    }

                }

            }
        }

        return array(
            "winGameCount" => $winCount,
            "totalGameCount" => $total,
            "validAttackCount" => $validAttackCount,
            "attackCount" => $attackCount,
            "totalGameTime" => $gameTime / 60,
            "circleGraphRate" => $circle_graph_rate,
            "barGraphRate" => $bar_graph_rate,
            "competitorCircleGraphRate" => $competitor_circle_graph_rate,
        );
    }

    // private function calculateAttacks(Game $game){
    //     $attackCount = 0;
    //     $validAttackCount = 0;
    //     $skillRate = [];
    //     $validSkillRate = [];

    //     foreach($game->attacks() as $attack){
    //         $attackCount += 1;
    //         $skillName = $attack->skill()->name;

    //         if(!($skillRate[$skillName])){
    //             $skillRate += array($skillName => 1);
    //         }else{
    //             $skillRate[$skillName] += 1;
    //         }
    //         if($attack->valid){
    //             $validAttackCount += 1;

    //             if(!($validSkillRate[$skillName])){
    //                 $validSkillRate += array($skillName => 1);
    //             }else{
    //                 $validSkillRate[$skillName] += 1;
    //             }
    //         }
    //     }

    //     return array(
    //         "validAttackCount" => $validAttackCount,
    //         "attackCount" => $attackCount,
    //         "skillRate" => $skillRate,
    //         "validSkillRate" => $validSkillRate,
    //     );
    // }

}
