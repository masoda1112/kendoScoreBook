<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

// Route::group(["middleware" => ["auth:api"]], function () {
//     Route::get("/{userName}", [UserController::class, "index"]);
//     Route::get("/{userName}/games", [UserController::class, "getGameIndex"]);
//     Route::get("/{userName}/{gameId}", [UserController::class, "getGame"]);
//     Route::post("/{userName}/add", [UserController::class, "addGame"]);
//     Route::post('/logout', [UserController::class, "logout"]);
// });
