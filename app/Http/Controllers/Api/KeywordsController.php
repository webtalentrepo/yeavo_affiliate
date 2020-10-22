<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KeywordsController extends Controller
{
    protected $topUrl;
    protected $gKeyUrl;

    public function __construct()
    {
        $this->topUrl = 'https://www.bing.com/webmasters/api/keywordresearch/topsearchurls';
        $this->gKeyUrl = 'https://gkeywords.nattyorganics.com/Google-Keywords/public/api/findKeywords';
    }

    public function testCall(Request $request)
    {

    }

    public function getKeywordData(Request $request)
    {
        $re = [];
        $re_keys = [];
        $rank_re = [];
        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');

            if (Cache::has($keyword)) {
                $re = json_decode(Cache::get($keyword));
                $re_keys = json_decode(Cache::get('RE_KEYS_' . $keyword));
            } else {
                $data = $this->getGoogleKeywords($keyword);

                $re = $data['keywords'];

                if ($re && sizeof($re) > 0) {
                    $re_reset = $re;
                    $re = [];
                    $questions = config('services.questions');
                    var_dump($questions);
                    $i = 0;
                    foreach ($re_reset as $key => $row) {
                        $rr = (array)$row;
                        $exist = false;
                        if ($questions && sizeof($questions) > 0) {
                            foreach ($questions as $q_row) {
                                $s_str = strtolower($q_row);
                                preg_match("/{$s_str}(.+)/", strtolower($rr['name']), $match);
                                //                            var_dump($match);
                                //                            var_dump($s_str);
                                //                            var_dump($rr['name']);
                                if (isset($match[1])) {
                                    $exist = true;
                                    break;
                                }
                            }
                        }

                        if (!$exist) {
                            continue;
                        }

                        $re[$i] = $rr;
                        $re[$i]['index'] = $i;

                        $i++;
                    }
                }

                $re_keys = $data['related_keywords'];

                if ($re && sizeof($re) > 0) {
                    Cache::add($keyword, json_encode($re), 172800);
                }

                Cache::add('RE_KEYS_' . $keyword, json_encode($re_keys), 172800);
            }

            if (Cache::has('RANK_COL_KEYWORD_' . $keyword)) {
                $rank_re = json_decode(Cache::get('RANK_COL_KEYWORD_' . $keyword));
            } else {
                $rank_re = $this->fetchTopLinks($keyword);

                Cache::add('RANK_COL_KEYWORD_' . $keyword, json_encode($rank_re), 172800);
            }
        }

        return response()->json([
            'result'    => $re,
            'rank'      => $rank_re,
            're_keys'   => $re_keys,
            'pageCount' => sizeof($re)
        ]);
    }

    private function getGoogleKeywords($keyword)
    {
        $keyword = urlencode($keyword);

        $chnd = curl_init();
        curl_setopt($chnd, CURLOPT_URL, "{$this->gKeyUrl}/{$keyword}");
        curl_setopt($chnd, CURLOPT_POST, FALSE);
        curl_setopt($chnd, CURLOPT_FOLLOWLOCATION, TRUE);

        curl_setopt($chnd, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($chnd, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($chnd, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36');
        curl_setopt($chnd, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        $data = curl_exec($chnd);
        if (curl_error($chnd))
            print_r(curl_errno($chnd) . ' ' . curl_error($chnd));
        curl_close($chnd);

        return json_decode($data, true);
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
