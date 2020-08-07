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
}
