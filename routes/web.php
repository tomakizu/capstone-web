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

Route::get('/', function () {
    return view('home');
});

Route::get('/pems_bay', function () {
    return view('pems_bay');
});

Route::post('/pems_bay', function () {
    return view('pems_bay');
});

Route::get('/metr_la', function () {
    return view('metr_la');
});

Route::post('/metr_la', function () {
    return view('metr_la');
});