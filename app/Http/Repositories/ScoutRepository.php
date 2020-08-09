<?php


namespace App\Http\Repositories;


class ScoutRepository
{
    public function __construct()
    {

    }

    public function getClickBankData($params)
    {
        $dev_key = config('services.clickbank.dev_key');
        $clerk_key = config('services.clickbank.clerk_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickbank.com/rest/1.3/analytics/vendor/?&account=dbbrock1");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept:application/json", "Authorization:{$dev_key}:{$clerk_key}", "Page:" . $params['page']));
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $return = curl_exec($ch);

        $result = json_decode($return, true);

        curl_close($ch);

        return $result;
    }

    public function getCJProductsData($params)
    {
        $access_key = env('CJ_ACCESS_TOKEN');
        $companyId = '2632470';
        $qry = '{ products(companyId: "' . $companyId . '", offset: ' . $params['page'] . ', limit: ' . $params['limit'];
        if ($params['keywords'] && !is_null($params['keywords'])) {
            $qry .= ', keywords: ["' . $params['keywords'] . '"]';
        }
        $qry .= ') {resultList { id, title, link, price {amount, currency}, salePrice {amount, currency} }  } }';

        return $this->getCJResponse($qry);
    }

    public function getCJProductsCount($params)
    {
        $companyId = '2632470';
        $qry = '{ products(companyId: "' . $companyId . '", limit: 10000';
        if ($params['keywords'] && !is_null($params['keywords'])) {
            $qry .= ', keywords: ["' . $params['keywords'] . '"]';
        }
        $qry .= ') {totalCount, count  } }';

        return $this->getCJResponse($qry);
    }

    private function getCJResponse($qry)
    {
        $access_key = env('CJ_ACCESS_TOKEN');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ads.api.cj.com/query");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept:application/json",
            "Authorization: Bearer {$access_key}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);

        $return = curl_exec($ch);

        $result = json_decode($return, true);

        curl_close($ch);

        return $result;
    }
}
