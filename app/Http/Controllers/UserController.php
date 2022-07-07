<?php

namespace App\Http\Controllers;
use App\Models\User;
use Http\Controllers\SkillController;
use Http\Controllers\GameController;
use Http\Controllers\AttackController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function login(Request $request)
    {
      $credentials = $request->validate([
        "email" => ["required", "email"],
        "password" => ["required"],
      ]);
  
      if (Auth::attempt($credentials)) {
      $request->session()->regenerate();
      return response()->json(Auth::user());
    }
      return response()->json([], 401);
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
            'data' => $user,
            'message' => 'User registration completed',
            'error' => ''
        ];
 
        return response()->json( $json, Response::HTTP_OK);
    }

    public function logout (Request $request) {
        auth('sanctum')->user()->tokens()->delete();
        return response(['message' => 'You have been successfully logged out.'], 200);
    }

    public function create(Request $request){
        $user = User::create([
            'name' =>  $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $user;
    }

    public function index(){
        $user = Auth::id();
        $response = $this->buildResponseData($user);
        response()->json($response);
    }

    public function getGameIndex(){
        $user = User::find(Auth::id());
        $gameList = GameController::index($user);
        response()->json([
            'games'=> $gameList
        ]);
    }

    public function getGame(Request $request){
        $user = User::find(Auth::id());
        $gameOverView = GameController::show($user, $request->gameId);
        $skills = GameController::calculateSkillRate();
        response()->json([
            "overView" => $gameOverView,
            "skillRate" => $skills
        ]);
    }

    public function addGame(Request $request){
        $game = GameController::create($request);
        Auth::user()->games()->createMany([$request->all()]);

    }

    private function buildResponseData($user){
        // 完結：winRate,validAttackRate,attackCount
        // 補助：caluculateSkillRate,calculateValidSkillRate
        $winCount = 0;
        $total = 0;
        $gameTime = 0;
        $games = $user->games();
        $attaks = [];
        foreach ($games as $game) {
            $total += 1;
            $gameTime += $game->time;
            if($game->result_id == 0) $winCount += 1;
            $attaks = $this->calculateAttacks($game);
        }

        return array(
            "winGameCount" => $winCount,
            "totalGameCount" => $total,
            "validAttackCount" => $attaks["validAttackCount"],
            "attackCount" => $attaks["attackCount"],
            "totalGameTime" => $gameTime,
            "skillRate" => $attaks["skillRate"],
            "validSkillRate" => $attaks["validSkillRate"],
        );
    }

    private function calculateAttacks(Game $game){
        $attackCount = 0;
        $validAttackCount = 0;
        $skillRate = [];
        $validSkillRate = [];
        foreach($game->attacks() as $attack){
            $attackCount += 1;
            $skillName = $attack->skill()->name;

            if(!($skillRate[$skillName])){
                $skillRate += array($skillName => 1);
            }else{
                $skillRate[$skillName] += 1;
            }

            if($attack->valid){
                $validAttackCount += 1;

                if(!($validSkillRate[$skillName])){
                    $validSkillRate += array($skillName => 1);
                }else{
                    $validSkillRate[$skillName] += 1;
                }
            }
        }

        return array(
            "validAttackCount" => $validAttackCount,
            "attackCount" => $atackCount,
            "skillRate" => $skillRate,
            "validSkillRate" => $validSkillRate,
        );
    }

}
