<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use bingWebmaster\actions\GetKeywordStats;
use bingWebmaster\actions\GetRelatedKeywords;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KeywordsController extends Controller
{
    protected $topUrl;

    public function __construct()
    {
        $this->topUrl = 'https://ads.google.com/aw_keywordplanner/standalone/_/rpc/BatchService/Batch';
    }

    public function testCall(Request $request)
    {
        $keyword = urlencode('jump higher');
        $cookie = file_get_contents(public_path('cookie.txt'));

        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, "{$this->topUrl}?authuser=0&acx-v-bv=awn_keywordplanner_20201007_RC00&acx-v-clt=1602449168643&rpcTrackingId=BatchService.Batch%5BIdeasService.List%5D%3A5&f.sid=-8666915839625251000");
        curl_setopt($chnd, CURLOPT_POST, 1);
        curl_setopt($chnd, CURLOPT_POSTFIELDS, http_build_query([
            'hl' => 'en_US',
            '__lu' => '447660175',
            '__u' => '1766863575',
            '__c' => '6791416622',
            'f.sid' => '-8666915839625251000',
            'ps' => 'aw',
            '__ar' => '{"2":["{\"1\":{\"3\":{\"1\":\"581099678\"},\"5\":\"TABLE\"},\"2\":{\"1\":[\"text\",\"search_volume\",\"search_volume_trends\",\"competition\",\"ad_impression_share\",\"account_status\",\"is_in_plan\",\"is_in_account\",\"is_negative\",\"bid_min\",\"bid_max\",\"competition_index\",\"keyword_variants\"],\"2\":[{\"1\":\"keyword_seed\",\"2\":1,\"4\":[{\"6\":\"how to jump higher\"}]},{\"1\":\"currency_code\",\"2\":1,\"4\":[{\"6\":\"USD\"}]},{\"1\":\"language\",\"2\":1,\"4\":[{\"3\":\"1000\"}]},{\"1\":\"locations\",\"4\":[{\"3\":\"2840\"}]},{\"1\":\"network\",\"2\":1,\"4\":[{\"3\":\"2\"}]},{\"1\":\"search_volume_types\",\"2\":1,\"4\":[{\"3\":\"4\"},{\"3\":\"1\"},{\"3\":\"3\"},{\"3\":\"5\"}]},{\"1\":\"plan_id\",\"2\":1,\"4\":[{\"3\":\"251700062\"}]},{\"1\":\"location_segmentation\",\"2\":1,\"4\":[{\"3\":\"1\"}]},{\"1\":\"skip_location_chart\",\"2\":1,\"4\":[{\"1\":true}]}],\"3\":[{\"1\":\"text\",\"2\":2}],\"4\":{\"1\":{\"1\":2016,\"2\":9,\"3\":1},\"2\":{\"1\":2020,\"2\":8,\"3\":31}},\"7\":{\"1\":0,\"2\":500},\"14\":true},\"3\":[{\"1\":\"AWN_KP_KWI_TO_CONTRA_MIGRATION\",\"2\":\"TRUE\"},{\"1\":\"ADD_CHARTS_REQUEST\",\"2\":\"TRUE\"},{\"1\":\"AWN_KP_PLANNING_API_SPS_MIGRATION\",\"2\":\"TRUE\"},{\"1\":\"INVERT_GROUPING_ENTITIES\",\"2\":\"TRUE\"}]}"],"3":[{"1":"ads.awapps.anji.proto.kp.IdeasService","2":"List"}]}',
            'activityContext' => 'IdeasNewView.SetPageSize',
            'requestPriority' => 'HIGH_LATENCY_SENSITIVE',
            'activityType' => 'INTERACTIVE',
            'activityId' => '2829629352738989',
            'uniqueFingerprint' => '-8666915839625251000_2829629352738989_2',
            'destinationPlace' => '/aw/keywordplanner/ideas/new',
        ]));
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36');
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            "content-length: 3077",
            "cookie: {$cookie}",
            "x-framework-xsrf-token: ADqOtbxbPJl4w9r1o_U0_-8zZTonaNSbww:1602484745952",
            "x-client-data: CIq2yQEIpbbJAQjBtskBCKmdygEIl7jKAQirx8oBCPXHygEI58jKAQjpyMoBCKPNygEIi87KAQjc1coBCMHXygEIn9jKAQj9l8sB
Decoded:
message ClientVariations {
  // Active client experiment variation IDs.
  repeated int32 variation_id = [3300106, 3300133, 3300161, 3313321, 3316759, 3318699, 3318773, 3318887, 3318889, 3319459, 3319563, 3320540, 3320769, 3320863, 3329021];
}",
            "x-framework-xsrf-token: ADqOtbxbPJl4w9r1o_U0_-8zZTonaNSbww:1602484745952"
        ]);
        $data = curl_exec($chnd);
        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        var_dump($data);
    }

    public function getKeywordData(Request $request)
    {

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
        curl_setopt($chnd, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36');
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

    }
}
