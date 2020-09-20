<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use bingWebmaster\actions\GetKeywordStats;
use bingWebmaster\actions\GetRelatedKeywords;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class KeywordsController extends Controller
{
    public function testCall(Request $request)
    {
        $client = new Client();

        $webMaster = new \bingWebmaster\client(config('services.bing_api_key'), $client);

//        $keywords_1 = $webMaster->request(new GetKeyword('diet', '', '', '2020-06-18T00:00:00.000Z', '2020-09-18T00:00:00.000Z'));

        $keywords = $webMaster->request(new GetKeywordStats('weight loss', '', ''));

//        print_r($keywords_1);
        print_r($keywords);
    }

    public function getKeywordData(Request $request)
    {
        $re = [];

        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');
            $client = new Client();

            $webMaster = new \bingWebmaster\client(config('services.bing_api_key'), $client);

            $cur_date = date('Y-m-d');
            $calc_date = date('Y-m-d', strtotime('+7 days', strtotime($cur_date)));
            $end_date = $cur_date . 'T00:00:00.000Z';
            $start_date = date('Y-m-d', strtotime('-6 months', strtotime($calc_date))) . 'T00:00:00.000Z';

            $keywords = $webMaster->request(new GetRelatedKeywords($keyword, '', '', $start_date, $end_date));

            if ($keywords) {
                foreach ($keywords as $key => $row) {
                    $re[$key]['keyword'] = $row->Query;
                    $re[$key]['broad_impressions'] = $row->BroadImpressions;
                    $re[$key]['impressions'] = $row->Impressions;

                    $stats = $webMaster->request(new GetKeywordStats($row->Query, '', ''));
                    $re[$key]['stats'] = [];
                    if ($stats) {
                        foreach ($stats as $key1 => $row1) {
                            $str = $row1->Date;
                            $str = preg_replace('/\D/', '', $str);
//                            $re[$key]['stats']['date'][$key1] = date('d M Y', $str);
                            $re[$key]['stats']['date'][$key1] = date('d M', intval($str) / 1000) . ': ' . $row1->Impressions;
                            $re[$key]['stats']['impressions'][$key1] = $row1->Impressions;
                        }
                    }
                }
            }
        }

        return response()->json([
            'result'    => $re,
            'pageCount' => sizeof($re)
        ]);
    }
}
