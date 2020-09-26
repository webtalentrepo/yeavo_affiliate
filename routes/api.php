<?php

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

Route::group([
    'middleware' => ['cors']
], function () {
    Route::post('/login', 'Api\AuthController@login');
    Route::post('/register', 'Api\AuthController@register');
    Route::post('/send-reset-password-link', 'Api\AuthController@sendResetPasswordLink');
    Route::post('/reset-password-by-token', 'Api\AuthController@setNewPasswordByToken');
    Route::get('/oauth', 'Api\AuthController@oauth');
    Route::get('/kajabi', 'Api\KajabiController@index');
    Route::post('/kajabi', 'Api\KajabiController@index');
    Route::post('/get-email-by-token', 'Api\AuthController@getUserByActivateToken');
});

Route::group([
    'middleware' => ['cors', 'auth:api']
], function () {
    Route::post('/logout', 'Api\AuthController@logout');
    Route::get('/users/me', 'Api\AuthController@getAuthUserInfo');

    // admin area
    Route::resource('roles', 'Admin\RolesController', ['names' => ['index' => 'roles'], 'except' => ['create', 'show']]);
    Route::resource('plans', 'Admin\PlansController', ['names' => ['index' => 'plans'], 'except' => ['create', 'show']]);
    Route::resource('users', 'Admin\UsersController', ['names' => ['index' => 'users'], 'except' => ['create', 'show']]);
    Route::resource('maps', 'Admin\MapsController', ['names' => ['index' => 'maps'], 'except' => ['create', 'show', 'edit', 'update']]);
    Route::resource('assets', 'Admin\AssetsController', ['names' => ['index' => 'assets'], 'except' => ['create', 'show', 'edit', 'update']]);

    // users area
    Route::post('/scout-data', 'Api\ScoutsController@index');
    Route::post('/child-data', 'Api\ScoutsController@getChildData');
    Route::get('/scout-data', 'Api\ScoutsController@index');
    Route::get('/keyword-data', 'Api\KeywordsController@getKeywordData');
    Route::post('/keyword-data', 'Api\KeywordsController@getKeywordData');
    Route::post('/keyword-data-trends', 'Api\KeywordsController@getKeywordTrends');
});
