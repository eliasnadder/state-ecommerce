<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Controller;
Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class,'register']);

Route::group(['middleware' => 'api',], function ($router) {
    Route::post('/logout', [UserController::class, 'logout']);
});
Route::get('/users', [UserController::class, 'index']);
