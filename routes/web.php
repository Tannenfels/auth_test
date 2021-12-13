<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'AuthController@show');
Route::post('/auth', 'AuthController@auth');
Route::post('/logout', 'AuthController@logout');
Route::get('/user/{id}', 'UserController@show');
