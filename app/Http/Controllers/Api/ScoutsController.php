<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\CommissionJunction;
use App\Http\Repositories\LinkShare;
use App\Http\Repositories\RakuteAPI;
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

        if ($re) {
            $reData = $re->map(function ($el) {
                if ($el->network == 'cj.com') {
                    $el->name = $el->site_id . ' - ' . $el->p_title;
                } else {
                    $el->name = $el->p_title;
                }
                $el->popularity = round($el->p_gravity, 2);
                $el->seven_day_epc = round($el->seven_day_epc, 2);
                $el->three_month_epc = round($el->three_month_epc, 2);
                $el->sale = round($el->p_commission, 2) . ($el->p_commission_unit == '%' ? '%' : ' ' . $el->p_commission_unit);

                if ($el->network == 'cj.com') {
                    $el->sign_up = 'https://members.cj.com/member/2536227/publisher/links/search/#!advertiserIds=' . $el->site_id;
//                    $el->sign_up = 'https://members.cj.com/member/accounts/publisher/affiliations/joinprograms.do?onJoin=clickSearch&advertiserId=' . $el->site_id;
                } elseif ($el->network == 'clickbank.com') {
                    $el->sign_up = 'https://accounts.clickbank.com/info/hoplinkGenerator.htm?vendor=' . $el->site_id;
                } else {
                    $el->sign_up = 'https://yeavo.com';
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

    public function getChildData(Request $request)
    {
        $params = [
            'keywords' => $request->input('search_str'),
            'sale_min' => $request->input('sale_min'),
            'sale_max' => $request->input('sale_max'),
            'parent_id' => $request->input('parent_id'),
            'limit' => $request->input('limit'),
        ];

        $reData = [];
        $pageCount = 0;

        $re = $this->scoutRepo->getChildData($params);

        if ($re) {
            $pageCount = ceil(sizeof($re->toArray()) / $params['limit']);

            $reData = $re->map(function ($el) {
                $el->sign_up = 'https://members.cj.com/member/accounts/publisher/affiliations/joinprograms.do?onJoin=clickSearch&advertiserId=' . $el->advertiser_id;

                $el->network = 'cj.com';

                return $el;
            });
        }

        return response()->json([
            'result' => 'success',
            'rows' => $reData,
            'pageCount' => $pageCount
        ]);
    }

    public function getTestCall()
    {
        $cj = new ShareSale();
        $c_detail = $cj->getMerchants([]);

        var_dump($c_detail['merchantSearchrecord']);
    }
}
