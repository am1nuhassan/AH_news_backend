<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferencesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/preferences', [PreferencesController::class, 'store'])->middleware('auth:sanctum');
Route::get('/personalized-feed', [ArticleController::class, 'fetchPersonalizedFeed'])->middleware('auth:sanctum');
Route::get('/preferences/options', [PreferencesController::class, 'getOptions'])->middleware('auth:sanctum');
Route::get('/preferences/saved', [PreferencesController::class, 'getSavedPreferences'])->middleware('auth:sanctum');

Route::get('/fetch-articles', [ArticleController::class, 'fetchArticles']);

Route::get('/articles', [ArticleController::class, 'index']);
