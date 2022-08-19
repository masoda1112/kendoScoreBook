<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Game;
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
        $gameList = $user->games();
        response()->json([
            'games'=> $gameList
        ]);
    }

    public function getGame(Request $request, $game_id){
        $user = $request->user();
        // game_idはパスから取る
        $resData = GameController::show($user, $game_id);
        response()->json($resData);
    }

    public function addGame(Request $request){
        // requestの形{competitor=>"", resultId=>"", time=> "", attacks => [{skill_id => ""}, {},{}] }
        $game = Game::create([
            'competitor_name' =>  $request->name,
            'result_id' => $request->email,
            'seconds' => $request->password,
        ]);
        // gamesにはcompetitorName,
        $attacks = [];
        foreach($request->attacks as $attack){
            $attacks += array(
                "skill_id" => $attack->skill_id,
                "game_id" => $game->id, 
                "competitor" => $request->competitor,
                "valid" => $request->valid
            );
        }

        Auth::user()->games()->createMany($attacks);

        response()->json($game ,Response::HTTP_OK);
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
        $skillRate = [];
        $validSkillRate = [];
        $competitorValidSkillRate = [];
        $games = $user->games();

        foreach ($games as $game) {
            $total += 1;
            $gameTime += $game->time;
            if($game->result_id == 0) $winCount += 1;
            $gameAttacks += $this->calculateAttacks($game);
            // ここでrate系の計算の分は繰り返し処理しておく
            foreach($game->attacks() as $attack){
                $attackCount += 1;
                if(!($skillRate[$skillName])){
                    $skillRate += array($skillName => 1);
                }else{
                    $skillRate[$skillName] += 1;
                }
                if($attack->valid){
                    if(!$attack->competitor){
                        $validAttackCount += 1;
                        if(!($validSkillRate[$skillName])){
                            $validSkillRate += array($skillName => 1);
                        }else{
                            $validSkillRate[$skillName] += 1;
                        }
                    }else{
                        if(!($competitorValidSkillRate[$skillName])){
                            $competitorValidSkillRate += array($skillName => 1);
                        }else{
                            $competitorValidSkillRate[$skillName] += 1;
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
            "totalGameTime" => $gameTime,
            "skillRate" => $skillRate,
            "validSkillRate" => $validSkillRate,
            "competitorValidSkillRate" => $competitorValidSkillRate,
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
