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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/date_location', 'FrontController@date_location');
Route::get('/init_wave', 'FrontController@init_wave');
Route::post('/get_today_data', 'FrontController@get_today_data');
Route::post('/get_for_location', 'FrontController@get_for_location');
Route::post('/get_json', 'FrontController@get_json');
Route::post('/get_for_realtime_wave', 'FrontController@get_for_realtime_wave');
Route::post('/get_for_realtime_wind', 'FrontController@get_for_realtime_wind');
Route::post('/get_all', 'FrontController@get_all');
