<?php


namespace App\Http\Repositories;


class ScoutRepository
{
    public function __construct()
    {

    }

    /**
     * @param $params
     * @return mixed
     */
    public function getClickBankData($params)
    {
        $dev_key = config('services.clickbank.dev_key');
        $clerk_key = config('services.clickbank.clerk_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickbank.com/rest/1.3/products/list/?&site=dbbrock1");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept:application/json", "Authorization:{$dev_key}:{$clerk_key}"));
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $return = curl_exec($ch);

        var_dump($return);

        $result = json_decode($return, true);

        curl_close($ch);

        return $result;
    }

    /**
     * Get cj products data
     * @param $params
     * @return mixed
     */
    public function getCJProductsData($params)
    {
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
        $access_key = config('services.cj_access_token');

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

    public function getRakutenProduct($params)
    {
//        $apiKey = config('services.rakuten_token');
        $apiKey = 'de46fda6e3f3beee56c1dc4a142844';
//
//        $client = new Client();
//        $client->setSecret('ec3da475028d2a8d761dfa3002cbecbc1564be57a8746709f3b7590dd80f0c34');
//        $client->setAffiliateId('3706879');
//
//        $parameter = ['hits' => $params['limit'], 'page' => $params['page']];
//
//        if ($params['keywords'] && !is_null($params['keywords'])) {
//            $parameter['keyword'] = $params['keywords'];
//        }
//
//        $response = $client->execute('ProductSearch', $parameter);
//        $responseCode = $response->getCode();
//
//        if ($responseCode != 200) {
//            $arReturn['message'] = $response->getMessage();
//        } else {
//            $data = $response->getData();
//            $arData = [];
//            $arData['totalRecords'] = $data['count'];
//            $arData['pageCount'] = $data['pageCount'];
//            $arData['data'] = $data['Items'];
//            $arReturn['data'] = $arData;
//        }
//
//        return $arReturn;
        $client = new RakuteAPI($apiKey);
        $parameters = ['max' => $params['limit'], 'pagenumber' => $params['page']];
        if ($params['keywords'] && !is_null($params['keywords'])) {
            $parameters['keyword'] = $params['keywords'];
        }

        return $client->productSearch($parameters);
    }
}
