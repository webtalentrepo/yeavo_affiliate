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

        $keywords_1 = $webMaster->request(new GetKeywordStats('celine dion weight loss', '', ''));

        $keywords = $webMaster->request(new GetRelatedKeywords('weight loss', '', '', '2020-06-18T00:00:00.000Z', '2020-09-18T00:00:00.000Z'));

        var_dump($keywords_1);
        var_dump($keywords);
    }
}
