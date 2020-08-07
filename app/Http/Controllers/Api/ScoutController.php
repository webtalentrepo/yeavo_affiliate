<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ScoutRepository;
use Illuminate\Http\Request;

class ScoutController extends Controller
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
            'affiliate_name' => $request->input('search_str'),
            'sale_min' => $request->input('sale_min'),
            'sale_max' => $request->input('sale_max'),
            'popular_min' => $request->input('popular_min'),
            'popular_max' => $request->input('popular_max'),
            'page' => $request->input('page'),
        ];

        $re = [];

        if ($sel_network == 'clickbank.com') {
            $re = $this->scoutRepo->getClickBankData($params);
//            if ($re && isset($re['orderData'])) {
////                $re['orderData']->map(function ($el) {
////                    return [
////                        'name' => $el['lineItemData']['productTitle']
////                    ];
////                });
//            }
        }

        return response()->json([
            'result' => 'success',
            'rows' => $re
        ]);
    }
}
