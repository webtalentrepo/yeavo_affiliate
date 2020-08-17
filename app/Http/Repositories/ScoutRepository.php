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

    public function setClickBankData($re, $link)
    {
        foreach ($re as $row) {
            $category = isset($row['Name']) ? $row['Name'] : '';

            $child_category = '';
            if (isset($row['Category']) && sizeof($row['Category']) > 0) {
                foreach ($row['Category'] as $cc_row) {
                    $child_category = isset($cc_row['Name']) ? $cc_row['Name'] : '';

                    if (isset($cc_row['Site']) && sizeof($cc_row['Site']) > 0) {
                        foreach ($cc_row['Site'] as $s_row) {
                            $this->saveClickBankData($s_row, $link, $category, $child_category);
                        }
                    }
                }
            } else {
                if (isset($row['Site']) && sizeof($row['Site']) > 0) {
                    foreach ($row['Site'] as $s_row) {
                        $this->saveClickBankData($s_row, $link, $category, $child_category);
                    }
                }
            }
        }
    }

    private function saveClickBankData($row, $link, $category, $child_category)
    {
        if (!isset($row['Id'])) {
            return;
        }

        $scout = $this->model()->where('site_id', $row['Id'])
            ->where('network', $link)
            ->first();

        if ($scout) {
            if ($scout->deleted_flag || $scout->edited_flag) {
                return;
            }
        } else {
            $scout = new Product();
        }

        $scout->network = $link;
        $scout->category = is_array($category) ? json_encode($category) : $category;
        $scout->child_category = is_array($child_category) ? json_encode($child_category) : $child_category;
        $scout->site_id = is_array($row['Id']) ? json_encode($row['Id']) : $row['Id'];
        $scout->popular_rank = isset($row['PopularityRank']) ? (is_array($row['PopularityRank']) ? json_encode($row['PopularityRank']) : $row['PopularityRank']) : 0;
        $scout->p_title = isset($row['Title']) ? (is_array($row['Title']) ? json_encode($row['Title']) : $row['Title']) : '';
        $scout->p_description = isset($row['Description']) ? (is_array($row['Description']) ? json_encode($row['Description']) : $row['Description']) : '';
        $scout->p_commission = isset($row['Commission']) ? (is_array($row['Commission']) ? json_encode($row['Commission']) : $row['Commission']) : 0;
        $scout->p_commission_unit = '%';
        $scout->p_gravity = isset($row['Gravity']) ? (is_array($row['Gravity']) ? json_encode($row['Gravity']) : $row['Gravity']) : 0;
        $scout->seven_day_epc = isset($row['AverageEarningsPerSale']) ? (is_array($row['AverageEarningsPerSale']) ? json_encode($row['AverageEarningsPerSale']) : $row['AverageEarningsPerSale']) : 0;
        $scout->three_month_epc = isset($row['InitialEarningsPerSale']) ? (is_array($row['InitialEarningsPerSale']) ? json_encode($row['InitialEarningsPerSale']) : $row['InitialEarningsPerSale']) : 0;
        $scout->earning_uint = 'USD';
        $scout->p_percent_sale = isset($row['PercentPerSale']) ? (is_array($row['PercentPerSale']) ? json_encode($row['PercentPerSale']) : $row['PercentPerSale']) : 0;
        $scout->deleted_flag = 0;
        $scout->edited_flag = 0;

        $scout->save();
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
