<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TribesController;
use App\Http\Controllers\VillagesController;
use App\Http\Controllers\WorldsController;

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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::get('/{world}/tribes', [TribesController::class, 'leaderboard']);
Route::get('/{world}/tribe', [TribesController::class, 'details']);
Route::get('/{world}/players', [PlayersController::class, 'leaderboard']);
Route::get('/{world}/player', [PlayersController::class, 'details']);
Route::get('/{world}/villages', [VillagesController::class, 'global_conquers']);
Route::get('/{world}/village', [VillagesController::class, 'details']);
Route::get('/{world}', [WorldsController::class, 'details']);