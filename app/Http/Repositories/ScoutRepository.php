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

        $qry = $qry->orderBy('popular_rank', 'desc')
            ->orderBy('p_commission', 'desc')
            ->get();

        return $qry;
    }
}
