<?php

namespace App\Http\Controllers;
use App\Models\User;
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

    }

    public function getGameIndex(){

    }

    public function getGame(){

    }

    public function addGame(){
        
    }

    private function calculateWinRate(){

    }

    private function calculateAttackCount(){

    }

    private function calculateValidAttackRate(){

    }

    private function calculateSkillRate(){
        
    }

    private function calculateValidSkillRate(){
        
    }


}
