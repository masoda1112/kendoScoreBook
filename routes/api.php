<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Http\Controllers\UserController;
use Http\Controllers\GameController;
use Http\Controllers\SkillController;
use Http\Controllers\AttackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 基本リクエストはUserControllerに集約し、内部で振り分ける

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/login", [UserController::class, "login"]);
Route::post("/logout", [UserController::class, "logout"]);
Route::post("/register", [UserController::class, "register"]);

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get("/{userName}", [UserController::class, "index"]);
    Route::get("/{userName}/games", [UserController::class, "getGameIndex"]);
    Route::get("/{userName}/{gameId}", [UserController::class, "getGame"]);
    Route::get("/{userName}/add", [UserController::class, "addGame"]);
  });
