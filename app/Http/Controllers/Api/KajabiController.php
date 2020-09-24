<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KajabiController extends Controller
{
    public function index(Request $request)
    {
        Log::info(json_encode($request->all()));
    }
}
