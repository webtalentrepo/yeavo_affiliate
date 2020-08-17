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

        $re = $this->scoutRepo->getScoutData($params, $sel_network);

//        if ($sel_network == 'clickbank.com') {
////            $re = file_get_contents(public_path('downloads/marketplace_feed_v2.xml'));
////
////            $xml = \simplexml_load_string($re, null, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA);
////
////            $json = json_encode($xml);
////            $array = json_decode($json, true);
////
////            print_r($array);
//        }
        if ($re) {
            $reData = $re->map(function ($el) use ($sel_network) {
                $el->name = $el->site_id . ' - ' . $el->p_title;
                $el->popularity = $el->popular_rank;
                $el->sale = $el->p_commission . ($el->p_commission_unit == '%' ? '%' : ' ' . $el->p_commission_unit);

                if ($sel_network == 'cj.com') {
                    $el->sign_up = 'https://members.cj.com/member/2536227/publisher/links/search/#!advertiserIds=' . $el->site_id;
                }

                return $el;
            });

            $pageCount = ceil(sizeof($re->toArray()) / $params['limit']);
        }

        return response()->json([
            'result' => 'success',
            'rows' => $reData,
            'pageCount' => $pageCount
        ]);
    }
}
