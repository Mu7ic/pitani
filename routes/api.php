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

    Route::post('/users/update', 'Api\CreateController@update_pass')->name('update');
    Route::get('/users/all', 'UsersController@users')->name('users_all');
    Route::get('/list/balance', 'UsersController@balance_list')->name('balance_list');
    Route::post('/users/add', 'Api\CreateController@add_user');
    Route::post('/users/add/balance', 'Api\CreateController@add_balance');

    Route::post('/users/select/food/day', 'Api\CreateController@food_day');
    Route::get('/select/food/day/{date}', 'UsersController@food_select');

    Route::post('/food/create', 'Api\CreateController@food_create')->name('food_create');
    Route::put('/food/update/{id}', 'Api\CreateController@food_update')->name('food_update');
});
