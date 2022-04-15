<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\TribesController;
use App\Http\Controllers\VillagesController;
use App\Http\Controllers\WorldsController;

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
    return view('home', ['page' => 'Home']);
});

Route::get('/{world}/tribes', [TribesController::class, 'index']);
Route::get('/{world}/tribe', [TribesController::class, 'show']);

Route::get('/{world}/players', [PlayersController::class, 'index']);
Route::get('/{world}/player', [PlayersController::class, 'show']);

Route::get('/{world}/villages', [VillagesController::class, 'index']);
Route::get('/{world}/village', [VillagesController::class, 'show']);

Route::get('/{world}', [WorldsController::class, 'index']);

Route::get('/{world}/privacy', function($world){
    return view('privacy', ['page' => 'privacy']);
});

Route::get('/{world}/test', function($world){
    return view('test', ['page' => 'test']);
});