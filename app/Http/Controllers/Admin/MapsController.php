<?php

namespace App\Http\Controllers\Admin;

use App\Facades\MyConfig;
use App\Http\Controllers\Controller;
use App\Map;
use Illuminate\Http\Request;

class MapsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $maps = Map::get();

        $re = [];
        if ($maps) {
            foreach ($maps as $key => $row) {
                $re[$key] = $row;
            }
        }

        return response()->json($re);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $input = [];
        for ($i = 0; $i < count((array)$request); $i++) {
            $input[$request[$i]['key']] = $request[$i]['value'];
        }

        MyConfig::saveSettings($input);

        return response()->json(['result' => 'success']);
    }
}
