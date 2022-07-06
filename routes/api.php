<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/login", [LoginController::class, "login"]);
Route::post("/logout", [LoginController::class, "logout"]);
Route::post("/register", [LoginController::class, "register"]);

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get("/{userName}", [UserController::class, "index"]);
    Route::get("/{userName}/games", [UserController::class, "getGameList"]);
    Route::get("/{userName}/{gameId}", [UserController::class, "getGame"]);
    Route::get("/{userName}/add", [UserController::class, "add"]);
  });
