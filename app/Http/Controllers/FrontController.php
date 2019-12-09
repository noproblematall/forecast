<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Measure;
use App\Cardinality;
use App\Endpoint;
use App\Location;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;


/**
 * API Class For Vue App
 * 
 */

class FrontController extends Controller
{

    /**
     * Show home page.
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        return view('vue');
    }

    /**
     * Get Location and Latest Dates
     * 
     * @return array
     */

    public function date_location()
    {
        $location = Location::get()->toArray();
        $real_date = $this->get_date();
        return [
            'location' => $location,
            'dates' => $real_date,
        ];

    }

    /**
     * Get Cardinality, Wave Data for Wave Page Initialize and Dates
     * 
     * @return array
     */

    public function init_wave()
    {
        $windir_mark = Cardinality::get()->toArray();
        $real_date = $this->get_date();
        $date_data = Location::first()->measures()->latest('forecastDate')->first()->forecastDate;
        $data = Location::first()->measures()->where('forecastDate', $date_data)->orderBy('localDate')->whereBetween('localDate', [$real_date[0], $real_date[1]])->get()->toArray();
        return [
            'data' => $data,
            'windir_mark' => $windir_mark,
            'dates' => $real_date,
        ];
    }

    /**
     * Get Data for Local Time.
     * 
     * @param  Request $request
     * @return array
     */

    public function get_today_data(Request $request)
    {
        $real_date = $this->get_date();
        $today = strtotime($request['today']);
        $location_id = $request['location_id'];
        $date_data = Measure::where('locationId', $location_id)->latest('forecastDate')->first()->forecastDate;
        $dates = Measure::where('locationId', $location_id)->select('localDate')->where('forecastDate', $date_data)->orderBy('localDate')->get()->unique('localDate')->toArray();
        $positions = [];
        $i = 0;
        foreach ($dates as $key => $value) {
            if($today < strtotime($value['localDate'])){
                array_push($positions, $dates[$key-5]['localDate']);
                array_push($positions, $value['localDate']);
                break;
            }else if($today == strtotime($value['localDate'])){
                array_push($positions, $value['localDate']);
                array_push($positions, $dates[$key+5]['localDate']);
            }
            $i++;
        }
        if(count($positions) == 0){
            return [
                'error' => 'no data'
            ];
        }else{
            $today_data = Measure::where('locationId', $location_id)->where('forecastDate', $date_data)->orderBy('localDate')->whereBetween('localDate', $positions)->get()->toArray();
            return [
                'today_data' => $today_data,
                'dates' => $real_date
            ];
        }
    }

    /**
     * Get Data for Locations
     * 
     * @param  Request $request
     * @return array
     */

    public function get_for_location(Request $request)
    {
        $id = $request['locationId'];
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];
        if($id && ($start_date == '' && $end_date == '')){
            $windir_mark = Cardinality::get()->toArray();
            $real_date = $this->get_date();
            $date_data = Measure::where('locationId', $id)->latest('forecastDate')->first()->forecastDate;
            $data = Measure::where('locationId', $id)->where('forecastDate', $date_data)->orderBy('localDate')->whereBetween('localDate', [$real_date[0], $real_date[1]])->get()->toArray();
            return [
                'data' => $data,
                'windir_mark' => $windir_mark,
                'dates' => $real_date,
            ];

        }else if($id && ($start_date != '' && $end_date != '')){
            $windir_mark = Cardinality::get()->toArray();
            $real_date = $this->get_date();
            $date_data = Measure::where('locationId', $id)->latest('forecastDate')->first()->forecastDate;
            $data = Measure::where('locationId', $id)->where('forecastDate', $date_data)->orderBy('localDate')->whereBetween('localDate', [$start_date, $end_date])->get()->toArray();
            return [
                'data' => $data,
                'windir_mark' => $windir_mark,
                'dates' => $real_date,
            ];
        }else {
            return [];
        }
    }

    /**
     * Get All Data for Excel and CSV.
     * 
     * @param  Request $request
     * @return array
     */

    public function get_json(Request $request)
    {
        $id = $request['locationId'];
        $date_data = Measure::where('locationId', $id)->latest('forecastDate')->first()->forecastDate;
        $data = Measure::where('locationId', $id)->where('forecastDate', $date_data)->orderBy('localDate')->get()->toJson();
        return $data;
    }

    /**
     * Get Data for All Data Graph
     * 
     * @param  Request $request
     * @return array
     */

    public function get_all(Request $request)
    {
        $id = $request['locationId'];
        $real_date = $this->get_date();
        $date_data = Measure::where('locationId', $id)->latest('forecastDate')->first()->forecastDate;
        $data = Measure::where('locationId', $id)->where('forecastDate', $date_data)->orderBy('localDate')->get()->toArray();
        return [
            'data' => $data,
            'dates' => $real_date
        ];
    }

    /**
     * Get Date
     * 
     * @return array
     */

    public function get_date()
    {
        $date_data = Location::first()->measures()->latest('forecastDate')->first()->forecastDate;
        $date_first = Location::first()->measures()->where('forecastDate', $date_data)->orderBy('localDate')->first()->localDate;
        $dates = Location::first()->measures()->select('localDate')->where('forecastDate', $date_data)->orderBy('localDate')->get()->unique('localDate')->toArray();
        $date = array(array('localDate' => $date_first));
        $j = 0;
        foreach ($dates as $key => $value) {
            if($j == 10){
                $j=0;
                array_push($date, $value);
            }
            $j++;
        }
        $real_date = array();
        foreach ($date as $key => $value) {
            array_push($real_date, $value['localDate']);
        }
        return $real_date;
    }

    /**
     * Get Real Time Wave Data
     * 
     * @param  Request $request
     * @return array
     */

    public function get_for_realtime_wave(Request $request)
    {
        $uri = $request['end_point'];
        $base_url = "https://vcmov.epsa.cl/api/v1/public/station/eoc_01/parameter/";
        $auth = env('REAL_AUTH');
        if($auth != ""){
            $headers = [
                'auth' => $auth,
                'Accept'        => 'application/json',
            ];
            $client = new Client(['base_uri' => $base_url]);
            $response = $client->request('GET', $uri, [
                'headers' => $headers
            ]);
            return $response;
        }else{
            return [
                'error' => 'auth failed'
            ];
        }
    }

    /**
     * Get Real Time Wind Data
     * 
     * @param  Request $request
     * @return array
     */

    public function get_for_realtime_wind(Request $request)
    {
        $uri = $request['end_point'];
        $base_url = "https://vcmov.epsa.cl/api/v1/public/station/emet_01/parameter/";
        $auth = env('REAL_AUTH');
        if($auth != ""){
            $headers = [
                'auth' => $auth,
                'Accept'        => 'application/json',
            ];
            $client = new Client(['base_uri' => $base_url]);
            $response = $client->request('GET', $uri, [
                'headers' => $headers
            ]);
            return $response;
        }else{
            return [
                'error' => 'auth failed'
            ];
        }
    }


}
