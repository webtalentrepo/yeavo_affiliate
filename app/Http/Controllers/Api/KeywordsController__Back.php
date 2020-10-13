<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use bingWebmaster\actions\GetKeywordStats;
use bingWebmaster\actions\GetRelatedKeywords;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

//use bingWebmaster\actions\GetQueryTrafficStats;
//use bingWebmaster\actions\GetRankAndTrafficStats;

//use bingWebmaster\actions\GetQueryTrafficStats;
//use bingWebmaster\actions\GetRankAndTrafficStats;

class KeywordsController__Back extends Controller
{
    protected $topUrl;

    public function __construct()
    {
        $this->topUrl = 'https://www.bing.com/webmasters/api/keywordresearch/topsearchurls';
    }

    public function testCall(Request $request)
    {
        $client = new Client();

        $webMaster = new \bingWebmaster\client(config('services.bing_api_key'), $client);

//        $keywords_1 = $webMaster->request(new GetKeyword('diet', '', '', '2020-06-18T00:00:00.000Z', '2020-09-18T00:00:00.000Z'));

        $keywords = $webMaster->request(new GetRelatedKeywords('weight loss', '', '', '2020-06-18T00:00:00.000Z', '2020-09-18T00:00:00.000Z'));

//        print_r($keywords_1);
        print_r($keywords);
    }

    public function getKeywordData(Request $request)
    {
        $re = [];
        $rank_re = [];

        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');
            if (Cache::has($keyword)) {
                $re = json_decode(Cache::get($keyword));
                $rank_re = json_decode(Cache::get('RANK_COL_KEYWORD_' . $keyword));
            } else {
                $client = new Client();

                $webMaster = new \bingWebmaster\client(config('services.bing_api_key'), $client);

                $cur_date = date('Y-m-d');
                $calc_date = date('Y-m-d', strtotime('+1 days', strtotime($cur_date)));
                $end_date = date('Y-m-d', strtotime('-1 days', strtotime($cur_date))) . 'T00:00:00.000Z';
                $start_date = date('Y-m-d', strtotime('-6 months', strtotime($calc_date))) . 'T00:00:00.000Z';

                $keywords = $webMaster->request(new GetRelatedKeywords($keyword, '', '', $start_date, $end_date));

                if ($keywords) {
                    foreach ($keywords as $key => $row) {
                        $re[$key]['keyword'] = $row->Query;
                        $re[$key]['broad_impressions'] = $row->BroadImpressions;
                        $re[$key]['impressions'] = $row->Impressions;

//                        $stats = $webMaster->request(new GetKeywordStats($row->Query, '', ''));
                        $re[$key]['stats'] = [];
                        $re[$key]['stats']['date'] = [];
                        $re[$key]['stats']['impressions'] = [];
//                        if ($stats) {
//                            foreach ($stats as $key1 => $row1) {
//                                $str = $row1->Date;
//                                $str = preg_replace('/\D/', '', $str);
////                            $re[$key]['stats']['date'][$key1] = date('d M Y', $str);
//                                $re[$key]['stats']['date'][$key1] = date('d M', intval($str) / 1000) . ': ' . $row1->Impressions;
//                                $re[$key]['stats']['impressions'][$key1] = $row1->Impressions;
//                            }
//                        }
                    }
                }

                Cache::add($keyword, json_encode($re), 1440);

                $rank_re = $this->fetchTopLinks($keyword);

                Cache::add('RANK_COL_KEYWORD_' . $keyword, json_encode($rank_re), 1440);
            }
        }

        return response()->json([
            'result'    => $re,
            'rank'      => $rank_re,
            'pageCount' => sizeof($re)
        ]);
    }

    private function fetchTopLinks($keyword)
    {
        $keyword = urlencode($keyword);
        $cookie = file_get_contents(public_path('cookie.txt'));

        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, "{$this->topUrl}?keyword={$keyword}&resultCount=10");
        curl_setopt($chnd, CURLOPT_POST, FALSE);
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);

        curl_setopt($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36');
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive',
            "cookie:$cookie"
        ]);
        $data = curl_exec($chnd);
        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        return json_decode($data, true);
    }

    public function getKeywordTrends(Request $request)
    {
        $keyword = $request->input('keyword');

        $re = [];
        $re['date'] = [];
        $re['impressions'] = [];

        if (Cache::has($keyword . '_COL_Stats')) {
            $re = json_decode(Cache::get($keyword . '_COL_Stats'));
        } else {
            $client = new Client();

            $webMaster = new \bingWebmaster\client(config('services.bing_api_key'), $client);

            $stats = $webMaster->request(new GetKeywordStats($keyword, '', ''));

            if ($stats) {
                foreach ($stats as $key1 => $row1) {
                    $str = $row1->Date;
                    $str = preg_replace('/\D/', '', $str);
                    $re['date'][$key1] = date('d M', intval($str) / 1000) . ': ' . $row1->Impressions;
                    $re['impressions'][$key1] = $row1->Impressions;
                }
            }

            Cache::add($keyword . '_COL_Stats', json_encode($re), 1440);
        }

        return response()->json([
            'result' => $re
        ]);
    }
}
