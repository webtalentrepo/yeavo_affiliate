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
        $checked_type = $request->input('checked_type');
        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');
            if ($keyword != '') {
                if (Cache::has($checked_type . '_' . $keyword)) {
                    $re = json_decode(Cache::get($checked_type . '_' . $keyword));
                    $re_keys = json_decode(Cache::get('RE_KEYS_' . $keyword));
                } else {
                    if (Cache::has($keyword)) {
                        $re = json_decode(Cache::get($keyword));
                        $re_keys = json_decode(Cache::get('RE_KEYS_' . $keyword));
                    } else {
                        $data = $this->getGoogleKeywords($keyword);

                        $re = $data['keywords'];
                        $re_keys = $data['related_keywords'];

                        if ($re && sizeof($re) > 0) {
                            Cache::add($keyword, json_encode($re), 864000);
                        }

                        Cache::add('RE_KEYS_' . $keyword, json_encode($re_keys), 864000);
                    }

                    if ($re && sizeof($re) > 0) {
                        $re_reset = $re;
                        $re = [];
                        $is_question = false;
                        $e_str = rtrim(strtolower($keyword));
                        $e_str = ltrim($e_str);
                        $questions = config('services.questions');
                        if ($questions && sizeof($questions) > 0) {
                            foreach ($questions as $q_row) {
                                $s_str = strtolower($q_row);
                                $t_ary1 = explode($s_str, strtolower($keyword));
                                if (isset($t_ary1[1])) {
                                    $is_question = true;
                                    $e_str = $t_ary1[1];
                                    break;
                                }
                            }
                        }

                        $i = 0;
                        foreach ($re_reset as $key => $row) {
                            $rr = (array)$row;
                            $exist = false;

                            if (!isset($rr['name'])) {
                                break;
                            }

                            if ($checked_type == 'broad') {
                                $e_ary = explode(' ', $e_str);
                                for ($j = 0; $j < count($e_ary); $j++) {
                                    if (strpos(strtolower($rr['name']), $e_ary[$j]) !== false) {
                                        $exist = true;
                                        break;
                                    }
                                }
                            } elseif ($checked_type == 'non') {
                                $i_q = false;
                                if ($questions && sizeof($questions) > 0) {
                                    foreach ($questions as $q_row) {
                                        $s_str = strtolower($q_row);
                                        $t_ary = explode($s_str, strtolower($rr['name']));
                                        if (isset($t_ary[0]) && $t_ary[0] == '' && isset($t_ary[1])) {
                                            $i_q = true;

                                            break;
                                        }
                                    }
                                }

                                if ($i_q || (strpos(strtolower($rr['name']), $e_str) === false)) {
                                    $exist = false;
                                } else {
                                    $exist = true;
                                }
                            } else {
                                if ($is_question) {
                                    $e_str1 = ltrim(strtolower($keyword));
                                    $e_str1 = rtrim($e_str1);
                                    if (strpos(strtolower($rr['name']), $e_str1) === false) {
                                        $exist = false;
                                    } else {
                                        $exist = true;
                                    }
                                } else {
                                    if ($questions && sizeof($questions) > 0) {
                                        foreach ($questions as $q_row) {
                                            $s_str = strtolower($q_row);
                                            $t_ary = explode($s_str, strtolower($rr['name']));
                                            if (isset($t_ary[0]) && $t_ary[0] == '' && isset($t_ary[1])) {
                                                if (strpos(strtolower($t_ary[1]), $e_str) === false) {
                                                    $exist = false;
                                                } else {
                                                    $exist = true;

                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if (!$exist) {
                                continue;
                            }

                            $re[$i] = $rr;
                            $re[$i]['index'] = $i;
                            $start_val = round($rr['month_search'] / 5000, 2);
                            if ($start_val > 1000) {
                                $start_str = round($start_val / 1000, 2) . 'M';
                            } else {
                                $start_str = $start_val . 'K';
                            }

                            $end_val = round($rr['month_search'] / 500, 2);
                            if ($end_val > 1000) {
                                $end_str = round($end_val / 1000, 2) . 'M';
                            } else {
                                $end_str = $end_val . 'K';
                            }

                            $re[$i]['month'] = $start_str . ' - ' . $end_str;
                            $re[$i]['trends'] = [];

                            if (isset($rr['trends']) && sizeof($rr['trends']) > 0) {
                                $k = 0;
                                foreach ($rr['trends'] as $r_t) {
                                    $month = $k === 35 ? date('n/y') : date('n/y', strtotime('-' . (35 - $k) . ' months'));
                                    $re[$i]['trends']['name'][$k] = $month . '(' . round($r_t * 1 / 1000) . 'K)';
                                    $re[$i]['trends']['value'][$k] = $r_t * 1;

                                    $k++;
                                }
                            }

                            $i++;
                        }
                    }

//                    if ($re && sizeof($re) > 0) {
//                        Cache::add($checked_type . '_' . $keyword, json_encode($re), 864000);
//                    }
                }

                if (Cache::has('RANK_COL_KEYWORD_' . $keyword)) {
                    $rank_re = json_decode(Cache::get('RANK_COL_KEYWORD_' . $keyword));
                } else {
                    $rank_re = $this->fetchTopLinks($keyword);

                    Cache::add('RANK_COL_KEYWORD_' . $keyword, json_encode($rank_re), 864000);
                }
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
