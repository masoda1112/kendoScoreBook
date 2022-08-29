<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(["middleware" => ["auth:api"]], function () {
    Route::get("/{userName}", [UserController::class, "index"]);
    Route::get("/skills", [SkillController::class, "skill_index"]);
    Route::get("/{userName}/games", [UserController::class, "getGameIndex"]);
    Route::get("/{userName}/{gameId}", [UserController::class, "getGame"]);
    Route::post("/{userName}/add", [UserController::class, "addGame"]);
    Route::post('/logout', [UserController::class, "logout"]);
});

Route::post('/register', [UserController::class,"register"]);
Route::post('/login', [UserController::class,"login"]);