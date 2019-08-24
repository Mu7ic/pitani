<?php

use Illuminate\Http\Request;


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


//Route::resource('/games', 'UsersController')->only([
//    'index', 'show', 'store', 'update', 'destroy'
//]);

Route::post('/login', 'Auth\LoginController@login')->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

    Route::post('/password/update', 'Api\CreateController@update_pass')->name('update');
    Route::post('/users/update', 'Api\CreateController@update_users')->name('update_users');

    Route::get('/users/all', 'UsersController@users')->name('users_all');
    Route::get('/users/all/{id}', 'UsersController@getUser')->name('getUser');

    Route::get('/check/{password}', 'UsersController@checkPass')->name('checkPass');

    Route::get('/list/balance', 'UsersController@balance_list')->name('balance_list');

    Route::get('/users/balance/{id}', 'UsersController@balance')->name('balance');

    Route::post('/users/add', 'Api\CreateController@add_user')->name('add_user');
    Route::post('/users/delete', 'Api\CreateController@user_delete')->name('delete_user');

    Route::post('/users/add/balance', 'Api\CreateController@add_balance')->name('add_balance');

    Route::get('/users/report/{id}/{start_date}/{end_date}', 'UsersController@getBalance')->name('getReports');

    Route::post('/users/select/food/day', 'Api\CreateController@food_day');
    Route::get('/select/food/day/{date}', 'UsersController@food_select');

    Route::post('/food/create', 'Api\CreateController@food_create')->name('food_create');
    Route::put('/food/update/{id}', 'Api\CreateController@food_update')->name('food_update');

    Route::get('/public/text', 'UsersController@getText')->name('getText');
    Route::post('/public/text', 'UsersController@updateText')->name('updateText');
});
