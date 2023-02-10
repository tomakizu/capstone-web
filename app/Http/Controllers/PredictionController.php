<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public static function PEMSPrediction(Request $request) {
        $data = array();
        $data['date'] = $request->date;
        return view('pems_bay', $data);
    }

    public static function METRPrediction(Request $request) {
        $data = array();
        $data['date'] = $request->date;
        return view('metr_la', $data);
    }
}
