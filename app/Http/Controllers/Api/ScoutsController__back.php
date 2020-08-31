<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ScoutRepository;
use App\Http\Repositories\ShareSale;
use Illuminate\Http\Request;

class ScoutsController__back extends Controller
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
            'keywords'    => $request->input('search_str'),
            'sale_min'    => $request->input('sale_min'),
            'sale_max'    => $request->input('sale_max'),
            'popular_min' => $request->input('popular_min'),
            'popular_max' => $request->input('popular_max'),
            'page'        => $request->input('page'),
            'limit'       => $request->input('limit'),
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
//            $re = $this->scoutRepo->getCJProductsData($params);
//
//            if ($re && isset($re['data']) && isset($re['data']['products']) && isset($re['data']['products']['resultList'])) {
//                foreach ($re['data']['products']['resultList'] as $key => $row) {
//                    $reData[$key] = [
//                        'name' => $row['title'],
//                        'sale' => $row['salePrice'] ? $row['salePrice']['amount'] . ' ' . $row['salePrice']['currency'] : $row['price']['amount'] . ' ' . $row['price']['currency'],
//                        'popularity' => '',
//                        'network' => $sel_network,
//                        'sign_up' => $row['link'],
//                    ];
//                }
//
//                $p_re = $this->scoutRepo->getCJProductsCount($params);
//
//                $pageCount = round($p_re['data']['products']['count'] / $params['limit']);
//            }

//            $cj = new CommissionJunction();
//            $re = $cj->getMerchants($params);
//
//            if ($re && isset($re['advertisers'])) {
//                $pageCount = ceil($re['advertisers']['@attributes']['total-matched'] / $params['limit']);
//
//                if (isset($re['advertisers']['advertiser'])) {
//                    foreach ($re['advertisers']['advertiser'] as $key => $row) {
//                        $sale = '';
//                        $c_name = '';
//                        if (isset($row['actions'])) {
//                            if (isset($row['actions']['action'])) {
//                                if (isset($row['actions']['action']['commission'])) {
//                                    if (isset($row['actions']['action']['name'])) {
//                                        $c_name = $row['actions']['action']['name'];
//                                    }
//
//                                    if (isset($row['actions']['action']['commission']['default'])) {
//                                        $sale = $row['actions']['action']['commission']['default'];
//                                    }
//                                } else {
//                                    if (isset($row['actions']['action'][0]) && isset($row['actions']['action'][0]['commission'])) {
//                                        $sale = $row['actions']['action'][0]['commission']['default'];
//
//                                        if (isset($row['actions']['action'][0]['name'])) {
//                                            $c_name = $row['actions']['action'][0]['name'];
//                                        }
//                                    }
//                                }
//                            }
//                        }
//
//                        if (!is_array($row['advertiser-id']) && !is_array($row['advertiser-name'])) {
//                            $name = $row['advertiser-id'] . ' - ' . $row['advertiser-name'];
//                        } else {
//                            $name = '';
//                            if (is_array($row['advertiser-id'])) {
//                                if (sizeof($row['advertiser-id']) > 0) {
//                                    $name .= $row['advertiser-id'][0] . ' - ';
//                                }
//                            } else {
//                                $name .= $row['advertiser-id'] . ' - ';
//                            }
//
//                            if (is_array($row['advertiser-name'])) {
//                                if (sizeof($row['advertiser-name']) > 0) {
//                                    $name .= $row['advertiser-name'][0];
//                                } else {
//                                    $name .= $c_name;
//                                }
//                            } else {
//                                $name .= $row['advertiser-name'];
//                            }
//                        }
//
//                        $reData[$key] = [
//                            'name' => $name,
//                            'popularity' => $row['network-rank'],
//                            'sale' => $sale,
//                            'network' => $sel_network,
//                            'sign_up' => $row['program-url'],
//                            'details' => $row
//                        ];
//                    }
//                }
//            }
        } elseif ($sel_network == 'Rakuten Linkshare') {
            $re = $this->scoutRepo->getRakutenProduct($params);

            if ($re && isset($re['item'])) {
                $pageCount = ceil($re['TotalMatches'] / $params['limit']);

                foreach ($re['item'] as $key => $row) {
                    $reData[$key] = [
                        'name'       => $row['mid'] . ' - ' . $row['merchantname'] . '(' . $row['productname'] . ')',
                        'popularity' => '',
                        'network'    => $sel_network,
                        //                        'sale' => 'https://cli.linksynergy.com/cli/publisher/programs/apply_confirmation.php',
                        'sale'       => $row['saleprice'],
                        'sign_up'    => $row['linkurl'],
                        'details'    => $row,
                    ];
                }
            }
//            $rtLs = new LinkShare();
//
//            $re = $rtLs->getMerchants($params);
//
//            print_r($re);
        } elseif ($sel_network == 'shareasale.com') {
            $ss = new ShareSale();

            $re = $ss->getMerchants($params);

            print_r($re);
        }

        return response()->json([
            'result'    => 'success',
            'rows'      => $reData,
            'pageCount' => $pageCount
        ]);
    }
}
