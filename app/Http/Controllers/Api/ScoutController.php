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

        if ($sel_network == 'clickbank.com') {
            $reData = $this->scoutRepo->getClickBankData($params);
//            if ($re && isset($re['orderData'])) {
////                $re['orderData']->map(function ($el) {
////                    return [
////                        'name' => $el['lineItemData']['productTitle']
////                    ];
////                });
//            }
        } elseif ($sel_network == 'cj.com') {
            $re = $this->scoutRepo->getCJProductsData($params);

            if ($re && isset($re['data']) && isset($re['data']['products']) && isset($re['data']['products']['resultList'])) {
                foreach ($re['data']['products']['resultList'] as $key => $row) {
                    $reData[$key] = [
                        'name' => $row['title'],
                        'sale' => $row['price']['amount'] . ' ' . $row['price']['currency'],
                        'popularity' => '',
                        'network' => $sel_network,
                        'sign_up' => $row['link'],
                    ];
                }

                $p_re = $this->scoutRepo->getCJProductsCount($params);

                $pageCount = round($p_re['data']['products']['count'] / $params['limit']);
            }
        }

        return response()->json([
            'result' => 'success',
            'rows' => $reData,
            'pageCount' => $pageCount
        ]);
    }
}
