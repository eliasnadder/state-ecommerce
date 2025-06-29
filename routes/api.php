<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PropertiesController;
use App\Http\Controllers\API\WantedPropertyController;
use App\Http\Controllers\API\OfficeController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\RequestController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\OfficeFollowerController as APIOfficeFollowerController;
use App\Http\Controllers\OfficeFollowerController;

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class,'register']);

Route::group(['middleware' => 'api',], function ($router) {
    Route::post('/logout', [UserController::class, 'logout']);
});


Route::group(['middleware' => ['api', 'jwt.auth'], 'prefix' => 'user'], function () {

    Route::post('/propertyStore', [PropertiesController::class, 'propertyStore']);
    Route::get('/properties/availability', [PropertiesController::class, 'availability']);
    Route::post('/payad', [PropertiesController::class, 'receiveCard']);

    Route::post('/WonProStore', [WantedPropertyController::class, 'WonProStore']);

    Route::post('/updateProfile', [UserController::class, 'updateProfile']);
    Route::get('/getProfile', [UserController::class, 'getProfile']);

    Route::post('/addToFavorites', [FavoriteController::class, 'addToFavorites']);
    Route::post('/removeFromFavorites', [FavoriteController::class, 'removeFromFavorites']);
    Route::get('/getFavorites', [FavoriteController::class, 'getFavorites']);

    Route::get('/rateOffice/{office_id}', [ReviewController::class, 'rateOffice']);

    Route::get('/getPendingRequests', [RequestController::class, 'getPendingRequests']);
    Route::get('/getacceptedRequests', [RequestController::class, 'getacceptedRequests']);
    Route::get('/getrejectedRequests', [RequestController::class, 'getrejectedRequests']);

    Route::post('/OfficeStore', [OfficeController::class, 'OfficeStore']);
    Route::get('/followOffice/{Id}', [OfficeController::class, 'followOffice']);

});

Route::group(['prefix' => 'user'], function () {
    Route::get('/getRecentOffers', [PropertiesController::class, 'getRecentOffers']);
    Route::get('/getPropertyVideos', [PropertiesController::class, 'getPropertyVideos']);
    Route::get('/properties', [PropertiesController::class, 'index']);
    Route::get('/properties/show/{id}', [PropertiesController::class, 'show']);
    Route::get('/properties/search/{ad_number}', [PropertiesController::class, 'searchByAdNumber']);
    Route::get('/properties/filter', [PropertiesController::class, 'filter']);

    Route::get('/getRating/{office_id}', [ReviewController::class, 'getRating']);

    Route::get('/officeProperties/{id}', [OfficeController::class, 'officeProperties']);
    Route::get('/getOfficePropertyCount/{id}', [OfficeController::class, 'getOfficePropertyCount']);
    Route::get('/getAllOfficePropertyVideos/{id}', [OfficeController::class, 'getAllOfficePropertyVideos']);
    Route::get('/showoffice/{Id}', [OfficeController::class, 'show']);
    Route::get('/getOfficeViews/{Id}', [OfficeController::class, 'getOfficeViews']);
    Route::get('/getFollowersCount/{Id}', [OfficeController::class, 'getFollowersCount']);
    Route::get('/getOfficeFollowers/{id}', [APIOfficeFollowerController::class, 'GetOfficeFollowers']);
});


Route::group(['middleware' => ['admin', 'jwt.auth'], 'prefix' => 'admin'], function () {
    Route::get('/getOfficeWantedProperties', [OfficeController::class, 'getOfficeWantedProperties']);
    Route::get('/getWanPro', [WantedPropertyController::class, 'getWanPro']);
    Route::post('/changePropertyStatus/{Id}', [PropertiesController::class, 'changePropertyStatus']);
    Route::get('/users', [UserController::class, 'index']);
});
