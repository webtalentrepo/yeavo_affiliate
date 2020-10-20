<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class keywordsApi extends Controller
{
    public $time_restrict;


    /**
     * Execute the loginflow to get the keywords
     *
     * @param bool $keywords
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKeywords($keywords = false)
    {
        $this->time_restrict = 5; // In Minutes;

        $accounts = json_decode(file_get_contents(__DIR__ . "/../../../accounts.json"));

        foreach ($accounts as $key => $value) {

            $gv = new \GoogleLoginCurl($value->email, $value->password, $value->recovery, $value->cc);
            $token = $this->getTopKeywords($value->email, $keywords);
            $rkey = $this->getRelatedKeywords($value->email, $keywords);

            return response()->json(["keywords" => $token, "related_keywords" => $rkey]);
        }

        return response()->json(["keywords" => '', "related_keywords" => '']);
    }

    public function getTopKeywords($login, $keywords)
    {

        global $_BASE_URL;
        $keyword = empty($keywords) ? 'Nike' : $keywords;
        $data = $this->fetch_remote_api('https://ads.google.com/aw_keywordplanner/standalone/_/rpc/BatchService/Batch?authuser=0&acx-v-bv=awn_keywordplanner_20201014_RC00&rpcTrackingId=BatchService.Batch%5BIdeasService.List%2CIdeasService.Charts%5D%3A1', $keyword, $login);
        // return $data;
        $data = @json_decode($data->{'2'}[0]);
        $response = [];
        if (isset($data->{'2'}->{'1'})) {
            foreach ($data->{'2'}->{'1'} as $key => $value) {
                if (isset($value->{'1000'})) {
                    $response[$key]['name'] = $value->{'1'};
                    $response[$key]['month_search'] = $value->{'1000'};
                    $response[$key]['bid_low'] = isset($value->{'1011'}) ? round($value->{'1011'} / 1000000, 2) : 'NA';
                    $response[$key]['bid_high'] = isset($value->{'1012'}) ? round($value->{'1012'} / 1000000, 2) : 'NA';
                    $response[$key]['competition'] = ($value->{'1001'} == 3) ? 'High' : ($value->{'1001'} == 2 ? 'Medium' : 'Low');
                    $response[$key]['competition_index'] = isset($value->{'1028'}) ? $value->{'1028'} : 0;
                }
            }
            return $response;
        } else
            return ["data" => []];

    }

    public function fetch_remote_api($url, $keyword, $login)
    {

        $XTOKEN = $this->getXsrfToken($login);
        $POSTDATA = file_get_contents(__DIR__ . "/../../../gk.md");
        $POSTDATA = str_replace("__KEYWORD_SEARCH_GOOGLE__", $keyword, $POSTDATA);
        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, $url);
        curl_setopt($chnd, CURLOPT_POST, TRUE);
        curl_setopt($chnd, CURLOPT_POSTFIELDS, $POSTDATA);
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);
        // Required to fetch the webmaster URL
        curl_setopt($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36');
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive',
            // "cookie:$ASPCOOKIE",
            "x-framework-xsrf-token:$XTOKEN"
        ]);
        curl_setopt($chnd, CURLOPT_COOKIEJAR, storage_path('gvoicecookies/') . $login . '.txt');
        curl_setopt($chnd, CURLOPT_COOKIEFILE, storage_path('gvoicecookies/') . $login . '.txt');
        $data = curl_exec($chnd);
        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        return json_decode($data);
    }

    /*
    /   Get the XSRF Token
    /
    */

    public function getXsrfToken($login)
    {

        $tokenURL = "https://ads.google.com/aw/keywordplanner/ideas/";
        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, $tokenURL);
        curl_setopt($chnd, CURLOPT_POST, FALSE);
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);
        // Required to fetch the webmaster URL
        // curl_setopt ($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36");
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive'
        ]);
        curl_setopt($chnd, CURLOPT_COOKIEJAR, storage_path('gvoicecookies/') . $login . '.txt');
        curl_setopt($chnd, CURLOPT_COOKIEFILE, storage_path('gvoicecookies/') . $login . '.txt');
        $data = curl_exec($chnd);
        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        file_put_contents(storage_path('gvoicecookies/') . 'sample.html', $data);
        preg_match("/xsrfToken: '(.+)',};window/", $data, $match);

        return @$match[1];
    }

    public function getRelatedKeywords($login, $keywords)
    {

        global $_BASE_URL;
        $keyword = empty($keywords) ? 'Nike' : $keywords;

        $data = $this->fetch_related_api('https://ads.google.com/aw_keywordplanner/standalone/_/rpc/IdeasService/Concepts?authuser=0&acx-v-bv=awn_keywordplanner_20201014_RC00&acx-v-clt=1602768017549&rpcTrackingId=IdeasService.Concepts%3A1', $keyword, $login);
        $data = @array_merge($data->{'2'}, $data->{'3'});

        return $data;
        // return json_encode(array("data" => ));
    }

    public function fetch_related_api($url, $keyword, $login)
    {

        $XTOKEN = $this->getXsrfToken($login);
        $POSTDATA = file_get_contents(__DIR__ . "/../../../grk.md");
        $POSTDATA = str_replace("__KEYWORD_SEARCH_GOOGLE__", $keyword, $POSTDATA);
        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, $url);
        curl_setopt($chnd, CURLOPT_POST, TRUE);
        curl_setopt($chnd, CURLOPT_POSTFIELDS, $POSTDATA);
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);
        // Required to fetch the webmaster URL
        curl_setopt($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36');
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            'Connection: keep-alive',
            // "cookie:$ASPCOOKIE",
            "x-framework-xsrf-token:$XTOKEN"
        ]);
        curl_setopt($chnd, CURLOPT_COOKIEJAR, storage_path('gvoicecookies/') . $login . '.txt');
        curl_setopt($chnd, CURLOPT_COOKIEFILE, storage_path('gvoicecookies/') . $login . '.txt');
        $data = curl_exec($chnd);

        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        return json_decode($data);
    }
}
