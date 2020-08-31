<?php

namespace Oara\Network\Publisher;
use DateTime;
use Exception;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use function count;
use function json_decode;

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
 * Export Class
 *
 * @author     Carlos Morillo Merino
 * @category   Skimlinks
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Skimlinks extends Network
{
    protected $_sitesAllowed = [];
    /**
     *  Account id - user
     * @var string
     */
    private $_account_id = null;
    /**
     * Public API Key
     * @var string
     */
    private $_apikey = null;
    /**
     * Private API Key
     * @var string
     */
    private $_privateapikey = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_client = new Access($credentials);
        $this->_account_id = $credentials['user'];
        $this->_privateapikey = $credentials['private_apikey'];
        $this->_apikey = $credentials['apikey'];
        $this->_country = $credentials['country'];
        $this->_id_site = $credentials['id_site'];
    }

    /**
     * @return array
     */
    public function getNeededCredentials()
    {
        $credentials = [];

        $parameter = [];
        $parameter["description"] = "User Log in";
        $parameter["required"] = true;
        $parameter["name"] = "User";
        $credentials["user"] = $parameter;

        $parameter = [];
        $parameter["description"] = "API Password";
        $parameter["required"] = true;
        $parameter["name"] = "API";
        $credentials["private_apikey"] = $parameter;

        return $credentials;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $connection = false;

        try {
            self::getMerchantList();
            $connection = true;
        } catch (Exception $e) {

        }

        return $connection;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $account_id = $this->_account_id;
        $apikey = $this->_apikey;
        $country = $this->_country;

        $a_merchants = [];

        //<JC> get Merchants with param 'country'
        $valuesFromExport = [
            new Parameter('apikey', $apikey),
            new Parameter('account_type', 'publisher_admin'),
            new Parameter('account_id', $account_id),
            new Parameter('country', $country)
        ];

        $a_merchants_country = $this->getMerchantsSkimlinks($valuesFromExport);

        //<JC> get favourite Merchants (with param 'favourite_type')
        $valuesFromExport = [
            new Parameter('apikey', $apikey),
            new Parameter('account_type', 'publisher_admin'),
            new Parameter('account_id', $account_id),
            new Parameter('favourite_type', 'favourite')
        ];

        $a_favourite_merchants = $this->getMerchantsSkimlinks($valuesFromExport);

        //<JC> Merge arrays...
        $a_merchants = array_merge($a_merchants_country, $a_favourite_merchants);
        //<JC> Remove duplicates
        $a_merchants = array_map("unserialize", array_unique(array_map("serialize", $a_merchants)));
        return $a_merchants;
    }

    /**
     * @param $a_params Parameters
     * @return array Merchants
     */
    public function getMerchantsSkimlinks($a_params): array
    {

        $a_merchants = [];
        $limit = 100; //default 25
        $offset = 0;

        array_push($a_params,
            new Parameter('limit', $limit),
            new Parameter('offset', $offset)
        );

        while (true) {
            $n_records = 0;
            $urls = [];

            foreach ($a_params as $key => $param) {
                if ($param->getKey() == 'offset') {
                    $param->setValue($offset);
                }
            }
            $urls[] = new Request("https://merchants.skimapis.com/v3/merchants?", $a_params);
            try {
                $exportReport = $this->_client->get($urls);
                $jsonArray = json_decode($exportReport[0], true);

                foreach ($jsonArray["merchants"] as $i) {
                    $n_records++;
                    $merchant = [];

                    $merchant['id'] = $i["id"];
                    $merchant['name'] = $i["name"];

                    $a_merchants[] = $merchant;
                }
                if ($n_records < $limit) {
                    break;
                }
                $offset += $limit;
                $limit = 100;
            } catch (Exception $e) {
                if ($limit == 1) {
                    $offset += $limit;
                } else {
                    $limit = (int)($limit / 2);
                }
                $n_records = $limit;
            }
        }

        return $a_merchants;
    }

    /**
     * @param null $merchantList
     * @param DateTime|null $dStartDate
     * @param DateTime|null $dEndDate
     * @return array
     * @throws Exception
     */
    public function getTransactionList($merchantList = null, DateTime $dStartDate = null, DateTime $dEndDate = null)
    {

        $totalTransactions = [];
        //<JC> end_date cannot be in the future
        if ($dEndDate > new DateTime()) {
            $dEndDate = new DateTime();
        }
        $privateapikey = $this->_privateapikey;

        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $token = md5($timestamp . $privateapikey);

        if (count($this->_sitesAllowed) == 0) {
            $valuesFromExport = [
                new Parameter('timestamp', $timestamp),
                new Parameter('token', $token),
                new Parameter('start_date', $dStartDate->format("Y-m-d")),
                new Parameter('end_date', $dEndDate->format("Y-m-d")),
            ];
            $totalTransactions = $this->processTransactions($valuesFromExport);
        } else {
            foreach ($this->_sitesAllowed as $site) {

                $valuesFromExport = [
                    new Parameter('timestamp', $timestamp),
                    new Parameter('token', $token),
                    new Parameter('start_date', $dStartDate->format("Y-m-d")),
                    new Parameter('end_date', $dEndDate->format("Y-m-d")),
                    //<JC> Filter commissions with a specific publisher domain ID - get value from Skimlinks > Account > SiteID
                    new Parameter('domain_id', $site)
                ];
                $totalTransactions = $this->processTransactions($valuesFromExport);
            }
        }

        return $totalTransactions;
    }

    private function processTransactions($valuesFromExport)
    {
        $totalTransactions = [];
        $limit = 100; //default 30
        $offset = 0;

        while (true) {
            $a_valuesFromExport = $valuesFromExport;

            array_push($a_valuesFromExport,
                new Parameter('limit', $limit),
                new Parameter('offset', $offset)
            );

            $n_records = 0;
            $urls = [];
            $urls[] = new Request("https://reporting.skimapis.com/publisher_admin/" . $this->_account_id . "/commission-report?", $a_valuesFromExport);
            try {
                $exportReport = $this->_client->get($urls);
                $jsonArray = json_decode($exportReport[0], true);

                foreach ($jsonArray["commissions"] as $i) {
                    $n_records++;
                    $transaction = [];

                    if (isset($i["merchant_details"])) {
                        $transaction['merchantId'] = $i["merchant_details"]["id"];
                    }
                    $transaction['unique_id'] = $i["commission_id"];

                    if (isset($i["transaction_details"])) {
                        //format datetime Y-m-d H:i:s
                        $transaction['date'] = $i["transaction_details"]["transaction_date"];
                        $transaction['last_updated'] = $i["transaction_details"]["last_updated"];

                        if (isset($i["transaction_details"]["basket"])) {
                            $transaction['amount'] = (double)$i["transaction_details"]["basket"]["order_amount"];
                            $transaction['commission'] = (double)$i["transaction_details"]["basket"]["publisher_amount"];
                            $transaction['currency'] = $i["transaction_details"]["basket"]["currency"];
                        }
                        $transactionStatus = $i["transaction_details"]["status"];
                        if ($transactionStatus == "active") {
                            $transaction ['status'] = Utilities::STATUS_CONFIRMED;
                        } elseif ($transactionStatus == "cancelled") {
                            $transaction ['status'] = Utilities::STATUS_DECLINED;
                        } else {
                            throw new Exception ("New status found {$transactionStatus}");
                        }
                    }
                    if (isset($i["click_details"])) {
                        if ($i["click_details"]["custom_id"] != null) {
                            $transaction['custom_id'] = $i["click_details"]["custom_id"];
                        }
                        $transaction['click_date'] = $i["click_details"]["date"];
                    }
                    if (isset($i["publisher_id"])) {
                        //<JC> publisher_id as providers.api_userid
                        $transaction['publisher_id'] = $i["publisher_id"];
                    }
                    if (isset($i["publisher_domain_id"])) {
                        //<JC> publisher_domain_id as providers.id_site
                        $transaction['publisher_domain_id'] = $i["publisher_domain_id"];
                    }
                    $totalTransactions[] = $transaction;
                }
                if ($n_records < $limit) {
                    break;
                }
                $offset += $limit;
                $limit = 100;
            } catch (Exception $e) {
                if ($limit == 1) {
                    $offset += $limit;
                } else {
                    $limit = (int)($limit / 2);
                }
                $n_records = $limit;
            }
        }
        return $totalTransactions;
    }

    /**
     * @param string $idSite
     */
    public function addAllowedSite(string $idSite)
    {
        $this->_sitesAllowed[] = $idSite;
    }


}
