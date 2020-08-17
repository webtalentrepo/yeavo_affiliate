<?php


namespace App\Http\Repositories;


use App\Models\Product;

class ScoutRepository
{
    public function __construct()
    {

    }

    public function model()
    {
        return app(Product::class);
    }

    /**
     * @param $params
     * @param $sel_network
     * @return mixed
     */
    public function getScoutData($params, $sel_network)
    {
        $qry = $this->model()->where('network', $sel_network);

        if (isset($params['keywords']) && !is_null($params['keywords']) && $params['keywords'] != '') {
            $keywordsAry = explode(' ', $params['keywords']);

            $qry = $qry->where(function ($q) use ($keywordsAry) {
                for ($i = 0; $i < count($keywordsAry); $i++) {
                    if ($i == 0) {
                        $q->where('category', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('child_category', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('p_description', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('p_title', 'like', '%' . $keywordsAry[$i] . '%');
                    } else {
                        $q->orWhere('category', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('child_category', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('p_description', 'like', '%' . $keywordsAry[$i] . '%')
                            ->orWhere('p_title', 'like', '%' . $keywordsAry[$i] . '%');
                    }
                }
            });
        }

        $s_min = 0;
        $s_max = 0;
        if (isset($params['sale_min']) && !is_null($params['sale_min']) && $params['sale_min'] != '') {
            $s_min = intval($params['sale_min']);
        }

        if (isset($params['sale_max']) && !is_null($params['sale_max']) && $params['sale_max'] != '') {
            $s_max = intval($params['sale_max']);
        }

        if ($s_min != 0) {
            $qry = $qry->where('p_commission', '>=', $s_min);
        }

        if ($s_max != 0) {
            $qry = $qry->where('p_commission', '<=', $s_max);
        }

        $p_min = 0;
        $p_max = 0;
        if (isset($params['popular_min']) && !is_null($params['popular_min']) && $params['popular_min'] != '') {
            $p_min = intval($params['popular_min']);
        }

        if (isset($params['popular_max']) && !is_null($params['popular_max']) && $params['popular_max'] != '') {
            $p_max = intval($params['popular_max']);
        }

        if ($p_min != 0) {
            $qry = $qry->where('popular_rank', '>=', $p_min);
        }

        if ($p_max != 0) {
            $qry = $qry->where('popular_rank', '<=', $p_max);
        }

        $qry = $qry->orderBy('popular_rank', 'desc')
            ->orderBy('p_commission', 'desc')
            ->get();

        return $qry;
    }
}
