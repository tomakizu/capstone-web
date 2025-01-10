<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class PredictionController extends Controller
{
    private static function getColor($percentage) {
        $color_scale = ['#00BF00', '#18C600', '#3CCC00', '#5ED200', '#7ED900', 
                        '#9DDF00', '#B9E600', '#D2EC00', '#E9F200', '#F9F900', 
                        '#FFF200', '#FFD700', '#FFBC00', '#FFA100', '#FF8600', 
                        '#FF6B00', '#FF5100', '#FF3600', '#FF1B00', '#FF0000'];
        return $color_scale[intval($percentage / 5)];
    }

    private static function insertRecords($road_network, $data, $timestamp) {
        $sensor_positions = DB::table('sensor_position')->where('road_network_id', $road_network->id)->get();
        for ($i = 0; $i < count($data['prediction']); $i++) {
            DB::table('predicted_data')->insert([
                'road_network_id' => $road_network->id,
                'timestamp'       => $timestamp,
                'sensor_id'       => $sensor_positions[$i]->id,
                'value'           => $data['prediction'][$i],
            ]);
            DB::table('actual_data')->insert([
                'road_network_id' => $road_network->id,
                'timestamp'       => $timestamp,
                'sensor_id'       => $sensor_positions[$i]->id,
                'value'           => $data['actual'][$i],
            ]);
        }
    }

    private static function visualize($road_network, $prediction_data, $timestamp) {
        self::insertRecords($road_network, $prediction_data, $timestamp);

        $sensor_positions = DB::table('sensor_position')->where('road_network_id', $road_network->id)->get();
        $image = Image::make(storage_path('image/' . $road_network->image_name));

        for ($i = 0; $i < count($prediction_data['prediction']); $i++) {
            $image->circle($road_network->point_size, $sensor_positions[$i]->x_position, $sensor_positions[$i]->y_position, function ($draw) use ($prediction_data, $i) {
                $draw->background(self::getColor($prediction_data['prediction'][$i]));
            });
        }

        $current_timestamp = time();
        $filename = $road_network->network_name . '_' . $current_timestamp . '.png';
        $image->save(public_path('image/' . $filename));

        return $filename;
    }

    private static function getPredictionData($road_network, $timestamp) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.100:8014');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['dataset' => $road_network, 'timestamp' => $timestamp]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = json_decode(str_replace('\n', '', curl_exec($ch)));
        curl_close($ch);

        $prediction_data = array_values(array_filter(explode(' ', trim($response->prediction, '[]'))));
        $actual_data     = array_values(array_filter(explode(' ', trim($response->actual, '[]'))));
        return ['prediction' => $prediction_data, 'actual' => $actual_data];
    }

    // private static function predict(Request $request) {
    //     $prediction_data = self::getPredictionData(strtolower(explode('_', $request->road_network)[0]), date('Y-m-d h:i:s', strtotime($request->input('date'))));
    //     $road_network = DB::table('road_network')->where('network_name', $request->road_network)->first();

    //     $data = array();
    //     $data['date'] = $request->date;
    //     $data['image'] = self::visualize($road_network, $prediction_data);
    //     $data['start_datetime'] = $road_network->start_datetime;
    //     $data['end_datetime'] = $road_network->end_datetime;

    //     return view(strtolower($request->road_network), $data);
    // }

    public static function PEMSPrediction(Request $request) {
        $timestamp = date('Y-m-d h:i:s', strtotime($request->input('date')));
        $prediction_data = self::getPredictionData('pems', $timestamp);
        // $return_from_backend = trim('[71.34483 67.94813 70.57571 67.57258 68.91597 66.81522 67.00373 68.13667 67.00373 69.11734 68.32563 67.85354 67.85354 68.815155 67.66615 68.51738 68.13667 68.23076 68.91596 68.32563 67.85354 67.57258 68.815155 67.00373 68.61515 69.11734 68.91597 68.51738 66.72143 67.75944 67.00373 69.42162 69.42162 69.626785 68.04249 67.85354 71.5394 68.04249 69.01676 69.727516 68.42134 68.32563 67.290405 66.81522 66.90743 67.85354 66.72143 69.42162 68.04249 67.85354 68.13667 69.11734 69.016754 68.71469 68.71469 69.21741 72.00959 68.23076 67.57258 68.13667 68.42134 70.21483 68.04249 68.815155 67.66615 68.42134 67.85354 68.13667 68.51738 67.47894 67.85354 67.47894 68.32563 67.47894 67.85354 68.42134 71.5394 67.75944 68.42134 70.11945 68.04249 67.85354 69.11734 67.85354 69.21741 68.04249 67.94813 67.94813 67.85354 67.94813 68.13667 69.11734 69.42162 67.94813 68.71469 67.57258 67.19532 68.91597 66.1419 70.57571 67.57258 69.016754 69.626785 68.91597 68.13667 68.42134 67.2904 71.91684 68.32563 72.0096 67.290405 68.91597 67.2904 67.3849 68.13667 69.31774 67.85354 67.94813 68.32563 68.13667 67.75944 72.60093 69.52473 67.85354 67.85354 68.23076 69.626785 68.13667 67.3849 67.94813 67.00373 66.43263 67.3849 70.11945 67.75944 67.94813 71.16814 67.47894 67.09993 67.94813 67.94813 71.2507 70.99911 67.09993 68.04249 68.91597 68.32563 68.61515 67.94813 65.16212 67.94813 70.57571 68.32563 69.21741 69.21741 69.727516 70.11945 69.42162 67.290405 66.52947 69.016754 67.94813 68.42134 67.57258 68.13667 68.32563 70.8307 70.48789 69.626785 70.8307 68.61514 68.71469 70.0221 68.71469 70.48789 71.0841 70.48789 68.23076 70.11945 69.42162 71.34483 69.626785 68.13667 70.74722 66.43263 67.85353 68.91597 68.91597 69.016754 69.21741 68.23076 67.94813 67.00373 70.914955 67.85354 68.23076 70.30775 67.47894 67.290405 69.42162 69.016754 68.61514 68.23076 69.11734 68.91597 68.91597 69.016754 70.11945 69.626785 67.57258 68.51739 68.91596 69.626785 68.91597 68.91597 68.91597 68.91596 71.5394 68.23076 68.61514 68.91597 68.91597 68.91597 68.91597 68.91597 68.91597 67.66615 68.04249 68.61514 67.3849 70.3986 68.91597 70.74723 71.53939 68.32563 68.13667 67.47894 69.01676 68.91597 68.91597 68.91597 68.23076 69.01676 66.72143 67.3849 68.23076 68.71469 68.91597 69.626785 68.91597 67.94813 70.48789 68.91597 71.729614 69.21741 69.01676 69.01676 70.57571 70.02211 69.626785 69.82672 69.421616 69.11734 69.31774 68.91597 69.11734 68.91597 68.91597 71.729614 68.13667 67.3849 68.51738 68.04249 68.91597 67.57258 68.32563 68.13667 68.23076 68.61514 67.94813 67.94813 68.71469 67.290405 68.04249 67.290405 68.91597 68.32563 68.13667 67.75944 67.00373 67.85354 65.261894 68.51739 67.290405 68.04249 68.61515 66.81522 67.38491 68.91597 68.32563 68.91597 65.261894 68.51738 68.51739 67.85353 68.04249 68.91597 67.3849 67.47894 68.61515 64.00571 69.11734 68.91597 68.71469 67.38491 68.91597 68.04249 68.91597 68.13667 69.31774 69.016754 70.48789 68.91597 71.08409 68.13667 ]', '[]');
        // $prediction_data = array_filter(explode(' ', $return_from_backend));

        $road_network = DB::table('road_network')->where('network_name', 'PEMS_BAY')->first();

        // prepare return data
        $return = array();
        $return['date'] = $request->date;
        $return['data'] = count($prediction_data);
        $return['image'] = self::visualize($road_network, $prediction_data, $timestamp);
        $return['start_datetime'] = $road_network->start_datetime;
        $return['end_datetime'] = $road_network->end_datetime;
        return view('pems_bay', $return);
    }

    public static function METRPrediction(Request $request) {
        $timestamp = date('Y-m-d h:i:s', strtotime($request->input('date')));
        $prediction_data = self::getPredictionData('metr', $timestamp);
        // $return_from_backend = trim('[66.39222 66.78264 68.2825 59.77755 62.297653 68.163155 59.213146 65.37409 65.499214 68.86514 56.595222 60.868496 62.418175 69.21056 63.107666 63.669876 31.379177 62.53706 59.32641 64.561676 69.44787 66.00639 54.61435 63.107666 65.62531 65.13878 62.53706 63.88868 69.21056 50.42579 61.55041 66.00639 68.042404 67.30054 68.749825 62.05316 62.811726 66.523155 62.1583 61.55041 67.4274 64.561676 55.91462 57.059597 66.13432 68.40058 64.331345 66.26295 56.368153 65.62531 66.00639 61.394413 69.21056 59.32641 61.394413 61.92878 44.538944 58.16898 62.88405 65.13878 66.523155 67.67528 68.2825 67.172104 57.822018 65.751854 65.13878 69.797554 68.634125 67.4274 64.67543 60.00713 64.44704 64.21564 69.21056 55.453583 61.92878 68.749825 67.796196 68.634125 68.980316 64.78861 66.00639 67.92012 59.549778 68.163155 66.00639 69.5687 67.172104 68.86514 68.40057 51.87374 68.042404 66.26295 65.37409 66.91148 69.09538 61.26497 59.77755 65.499214 66.39223 66.65382 61.92878 67.552505 67.552505 64.67543 64.21564 68.2825 67.4274 60.12162 68.749825 65.62531 67.172104 65.021324 68.042404 67.4274 64.331345 67.796196 66.78264 68.2825 68.980316 64.105316 67.92013 67.675285 65.13878 63.88868 55.685543 63.732796 66.91148 65.021324 67.042206 62.99652 68.2825 67.042206 66.78264 63.44603 66.78264 64.21564 64.90355 65.13878 66.523155 60.73392 64.331345 64.44704 56.595222 68.40057 63.44603 66.00639 68.749825 68.980316 65.62531 60.73392 64.21564 64.78861 61.55041 62.05316 53.34406 65.021324 56.140644 29.645689 64.90355 67.30054 68.86515 63.779655 66.00639 61.26497 69.68485 68.634125 67.4274 67.042206 65.878845 68.042404 64.105316 67.30054 66.26295 68.980316 68.163155 61.55041 64.44704 47.816032 66.91148 68.042404 69.44787 67.4274 63.88868 65.25628 68.517685 60.00713 65.878845 67.92012 64.90355 62.418175 65.62531 53.469032 63.779655 68.980316 31.379175 51.994934 69.21056 66.13432 67.172104 68.2825 53.719677 68.2825 67.172104 68.163155 56.712162]', '[]');
        // $prediction_data = array_filter(explode(' ', $return_from_backend));

        $road_network = DB::table('road_network')->where('network_name', 'METR_LA')->first();

        // prepare return data
        $return = array();
        $return['date'] = $request->date;
        $return['data'] = count($prediction_data);
        $return['image'] = self::visualize($road_network, $prediction_data, $timestamp);
        $return['start_datetime'] = $road_network->start_datetime;
        $return['end_datetime'] = $road_network->end_datetime;
        return view('metr_la', $return);
    }
}
