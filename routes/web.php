<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;

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

// Route::get('/', [PredictionController::class, 'PEMSPrediction']);

Route::get('/pems_bay', function () {
    $road_network = DB::table('road_network')->where('network_name', 'PEMS_BAY')->first();
    $data = array();
    if (!empty($road_network)) {
        $data['start_datetime'] = $road_network->start_datetime;
        $data['end_datetime'] = $road_network->end_datetime;
    }
    return view('pems_bay', $data);
});

Route::post('/pems_bay', [PredictionController::class, 'PEMSPrediction']);

Route::get('/metr_la', function () {
    $road_network = DB::table('road_network')->where('network_name', 'METR_LA')->first();
    $data = array();
    if (!empty($road_network)) {
        $data['start_datetime'] = $road_network->start_datetime;
        $data['end_datetime'] = $road_network->end_datetime;
    }
    return view('metr_la', $data);
});

Route::post('/metr_la', [PredictionController::class, 'METRPrediction']);