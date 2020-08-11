<?php
namespace Oara\Network\Publisher;

    /**
     * The goal of the Open Affiliate Report Aggregator (OARA) is to develop a set
     * of PHP classes that can download affiliate reports from a number of affiliate networks, and store the data in a common format.
     *
     * Copyright (C) 2016  Fubra Limited
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or any later version.
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU Affero General Public License for more details.
     * You should have received a copy of the GNU Affero General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     * Contact
     * ------------
     * Fubra Limited <support@fubra.com> , +44 (0)1252 367 200
     **/
/**
 * Api Class
 *
 * @author     Carlos Morillo Merino
 * @category   Belboon
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Belboon extends \Oara\Network
{
    private $_api_key;
    private $_user_id;

    private $BASE_PATH = "https://export.service.belboon.com";

    public function __construct($apiKey, $user_id)
    {
        $this->_api_key = $apiKey;
        $this->_user_id = $user_id;
    }

    /**
    * @return bool
    */
    public function checkConnection()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getNeededCredentials()
    {
        return [];
    }

    
    /**
     * @return array
     */
    public function getMerchantList()
    {
        $MERCHANT_LIST_PATH = "{$this->BASE_PATH}/{$this->_api_key}/mlist.csv";

        $rawMerchants = $this->callApi($MERCHANT_LIST_PATH);

        return array_map(function ($rawMerchant) {
            $merchant = [];

            $merchant["name"] = $rawMerchant["title"];
            $merchant["cid"] = $rawMerchant["mid"];

            return $merchant;
        }, $rawMerchants);
    }

    /**
     * @param null $merchantList
     * @param \DateTime|null $dStartDate
     * @param \DateTime|null $dEndDate
     * @return array
     */
    public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
    {
        $TRANSACTIONS_LIST_PATH = "{$this->BASE_PATH}/{$this->_api_key}/reporttransactions.csv";

        $params = [
            'filter[date_from]' => $dStartDate->format('d.m.Y'),
            'filter[date_to]' => $dEndDate->format('d.m.Y'),
            'filter[timerange_type]' => 'absolute',
            'filter[currencycode]' => 'EUR'
        ];

        $rawTransactions = $this->callApi($TRANSACTIONS_LIST_PATH, $params);

        if ($merchantList) {
            $rawTransactions = array_filter(
                $rawTransactions,
                function($rawTransaction) use ($merchantList) {
                    return $rawTransaction["advertiser_id"] == $merchantList;
                }
            );
        }

        return array_map(function ($rawTransaction) {
            $transaction = [];

            $transaction['unique_id'] = $rawTransaction["conversion_uniqid"];
            $transaction['merchantId'] = $rawTransaction["advertiser_id"];
            $transaction['date'] = $rawTransaction["conversion_tracking_time"];
            $transaction['click_date'] = $rawTransaction["click_time"];
            // $transaction['lastchangedate'] = $rawTransaction["lastchangedate"];

            $transaction['custom_id'] = $this->getTrackingCode($rawTransaction["click_subid"]);

            switch ($rawTransaction["conversion_status"]) {
            case '0':
            case '1':
                $transaction['status'] = \Oara\Utilities::STATUS_PENDING;
                break;
            case '2':
                $transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
                break;
            case '3':
                $transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                break;
            }
            $transaction['amount'] = \Oara\Utilities::parseDouble($rawTransaction["conversion_order_value"]);
            $transaction['commission'] = \Oara\Utilities::parseDouble($rawTransaction["conversion_commission_total"]);

            return $transaction;
        }, $rawTransactions);
    }

    public function getVouchers()
    {
        $VOUCHERS_LIST_PATH = "{$this->BASE_PATH}/{$this->_api_key}/vouchers.csv";

        $params = [
            'filter[uid]' => $this->_user_id
        ];

        $rawVouchers = $this->callApi($VOUCHERS_LIST_PATH, $params);

        return $rawVouchers;
    }

    private function callApi($path, $params = null)
    {
        $client = curl_init();
        $url = $path;
        if ($params) {
            $url = $url . "?" . http_build_query($params);
        }

        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($client);
        curl_close($client);

        $data = $this->convertCsvToArray($response);

        return $data;
    }

    private function convertCsvToArray($csvString)
    {
        $data = $this->str_getcsv($csvString);

        // remove last element, csv have a last \n, so last line is empty
        array_pop($data);
        
        array_walk($data, function (&$a) use ($data) {
            $a = array_combine($data[0], $a);
        });

        array_shift($data);

        return $data;
    }

    private function str_getcsv($input, $delimiter = ";", $enclosure = '"') {
        $memSize = 50 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$memSize", 'r+');
        fputs($fp, $input);
        rewind($fp);

        $data = [];
        while (($result = fgetcsv($fp, 1000, $delimiter, $enclosure)) !== FALSE)
        {
            $data[] = $result;
        }

        fclose($fp);
        return $data;
    }

    // there are 3 cases here...
    // 1: the code is a normal string
    // 2: the code is like "subid1=CODE
    // 3: the code is multiple like "subid1=CODE1+smc1=CODE2"

    // if present, we take the code
    // if there is the smc1 code as parameter we take that
    // if there is the subid1 code as parameter we take that
    private function getTrackingCode($rawString) {
        $trackingCode = null;

        if ($rawString != null) {
            $trackingCode = $rawString;
            if (strpos($trackingCode, '=')) {
                if(strpos($trackingCode, 'smc1=') !== false) {
                    $splittingString = explode('smc1=', $trackingCode)[1];
                    $trackingCode = explode('+', $splittingString)[0];
                } elseif(strpos($trackingCode, 'subid1=') !== false) {
                    $splittingString = explode('subid1=', $trackingCode)[1];
                    $trackingCode = explode('+', $splittingString)[0];
                }
            }
        }

        return $trackingCode;
    }
}
