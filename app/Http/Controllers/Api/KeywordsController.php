<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
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

    public function getTopUrls(Request $request)
    {
        $top_re = [];

        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');

            if (Cache::has('RANK_COL_KEYWORD_' . $keyword)) {
                $top_re = json_decode(Cache::get('RANK_COL_KEYWORD_' . $keyword));
            } else {
                $top_re = $this->fetchTopLinks($keyword);

                Cache::add('RANK_COL_KEYWORD_' . $keyword, json_encode($top_re), 864000);
            }
        }

        return response()->json([
            'rank' => $top_re,
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

    public function getKeywordData(Request $request)
    {
        $re = [];
        $checked_type = $request->input('checked_type');
        $is_question = $request->input('is_question');
        $keyword_str = $request->input('keyword_str');
        if ($request->has('search_str')) {
            $keyword = $request->input('search_str');

            if ($keyword != '') {
                if (Cache::has($checked_type . '_' . $keyword)) {
                    $re = json_decode(Cache::get($checked_type . '_' . $keyword));

                    return response()->json([
                        'result'    => $re,
                        'pageCount' => sizeof($re)
                    ]);
                } else {
                    $re = $this->getSearchData($keyword, $checked_type);
                    if (sizeof($re) > 0) {
                        Cache::add($checked_type . '_' . $keyword, json_encode($re), 864000);

                        return response()->json([
                            'result'    => $re,
                            'pageCount' => sizeof($re)
                        ]);
                    } else {
                        if (Cache::has($keyword)) {
                            $re = json_decode(Cache::get($keyword));
                        } else {
                            $data = $this->getGoogleKeywords($keyword);

                            $re = $data['keywords'];

                            if ($re && sizeof($re) > 0) {
                                Cache::add($keyword, json_encode($re), 864000);
                            }
                        }

                        if ($re && sizeof($re) > 0) {
                            $re_reset = $re;
                            $re = [];
                            $e_str = rtrim(strtolower($keyword_str));
                            $e_str = ltrim($e_str);
                            $questions = config('services.questions');
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
                                    $e_str1 = ltrim(strtolower($keyword));
                                    $e_str1 = rtrim($e_str1);
                                    $e_ary1 = explode($e_str, $e_str1);

                                    if (isset($e_ary1[1]) && $e_ary1[1] === '') {
                                        $e_str1 = $e_ary1[0];
                                        if (strpos(strtolower($rr['name']), $e_str) === false || strpos(strtolower($rr['name']), $e_str1) === false) {
                                            $exist = false;
                                        } else {
                                            $exist = true;
                                        }
                                    } else {
                                        $exist = false;
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
                                    if ($start_val < 1) {
                                        if ($start_val < 0.1) {
                                            $start_str = 10;
                                        } else {
                                            $start_str = 100;
                                        }
                                    } else {
                                        $start_str = $start_val . 'K';
                                    }
                                }

                                $end_val = round($rr['month_search'] / 500, 2);
                                if ($end_val > 1000) {
                                    $end_str = round($end_val / 1000, 2) . 'M';
                                } else {
                                    if ($end_val < 1) {
                                        if ($end_val < 0.1) {
                                            $end_str = 10;
                                        } else {
                                            $end_str = 100;
                                        }
                                    } else {
                                        $end_str = $end_val . 'K';
                                    }
                                }

                                $re[$i]['month'] = $start_str . ' - ' . $end_str;
                                $re[$i]['trends'] = [];

                                if (isset($rr['trends']) && sizeof($rr['trends']) > 0) {
                                    $k = 0;
                                    foreach ($rr['trends'] as $r_t) {
                                        $month = $k === 35 ? date('n/y') : date('n/y', strtotime('-' . (35 - $k) . ' months'));
                                        $re[$i]['trends']['name'][$k] = $month . '(' . round($r_t * 1 / 1000, 2) . 'K)';
                                        $re[$i]['trends']['value'][$k] = $r_t * 1;

                                        $k++;
                                    }
                                }

                                Keyword::insertGetId([
                                    'keywords'     => $keyword,
                                    'result'       => $re[$i]['name'],
                                    'type'         => $checked_type,
                                    'volume'       => $re[$i]['month'],
                                    'trend'        => json_encode($re[$i]['trends']),
                                    'state'        => $re[$i]['competition'],
                                    'bid_low'      => $re[$i]['bid_low'],
                                    'bid_high'     => $re[$i]['bid_high'],
                                    'competition'  => $re[$i]['competition_index'],
                                    'write_date'   => date('Y-m-d'),
                                    'updated_flag' => 0,
                                    'created_at'   => now(),
                                    'updated_at'   => now(),
                                ]);

                                $i++;
                            }
                        }

                        if ($re && sizeof($re) > 0) {
                            Cache::add($checked_type . '_' . $keyword, json_encode($re), 864000);
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

    private function getSearchData($keyword, $check_type)
    {
        $qry = Keyword::where('keywords', $keyword)->where('type', $check_type)->get();

        $re = [];
        if ($qry) {
            foreach ($qry as $key => $row) {
                $re[$key] = [
                    'name'              => $row->result,
                    'month'             => $row->volume,
                    'month_search'      => $row->volume,
                    'trend'             => json_decode($row->trend),
                    'trends'            => json_decode($row->trend),
                    'competition'       => json_decode($row->state, true),
                    'bid_low'           => $row->bid_low,
                    'bid_high'          => $row->bid_high,
                    'competition_index' => $row->competition,
                ];
            }
        }

        return $re;
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
}
