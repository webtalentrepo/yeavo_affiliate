<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\CommissionJunction;
use App\Http\Repositories\LinkShare;
use App\Http\Repositories\ScoutRepository;
use App\Http\Repositories\ShareSale;
use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Http\Request;

class ScoutsController extends Controller
{
    protected $scoutRepo;

    public function __construct(ScoutRepository $scoutRepository)
    {
        $this->scoutRepo = $scoutRepository;
    }

    public function index(Request $request)
    {
        $sel_network = $request->input('sel_network');
        $params = [
            'keywords' => $request->input('search_str'),
            'sale_min' => $request->input('sale_min'),
            'sale_max' => $request->input('sale_max'),
            'popular_min' => $request->input('popular_min'),
            'popular_max' => $request->input('popular_max'),
            'page' => $request->input('page'),
            'limit' => $request->input('limit'),
        ];

        $reData = [];
        $pageCount = 0;


        return response()->json([
            'result' => 'success',
            'rows' => $reData,
            'pageCount' => $pageCount
        ]);
    }
}
