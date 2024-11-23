<?php

use App\Http\Controllers\SpotifyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('generate_token', [SpotifyController::class, 'generateToken']);
Route::get('create', [SpotifyController::class, 'index']);
Route::get('search', [SpotifyController::class, 'search']);