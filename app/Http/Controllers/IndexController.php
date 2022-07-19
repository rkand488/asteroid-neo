<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function get_neo_data(Request $request){
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d|before_or_equal:end_date',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $response = Http::get("https://api.nasa.gov/neo/rest/v1/feed?start_date=$start_date&end_date=$end_date&api_key=n9DW5NZWG0HP9HSJAw9Zwtjisvo0kV1Rdjj9yRpI");
        return $response->json($key = null);
    }
}
