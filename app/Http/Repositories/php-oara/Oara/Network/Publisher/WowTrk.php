<?php

namespace Oara\Network\Publisher;
use DateTime;
use Exception;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use SimpleXMLElement;
use function implode;
use function simplexml_load_string;

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
 * @category   Wow
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class WowTrk extends Network
{

    private $_exportClient = null;
    private $_apiPassword = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $user = $credentials['user'];
        $password = $credentials['password'];
        $this->_apiPassword = $credentials['apiPassword'];
        $this->_client = new Access($credentials);


        //login through wow website
        $loginUrl = 'http://p.wowtrk.com/';
        $valuesLogin = [
            new Parameter('data[User][email]', $user),
            new Parameter('data[User][password]', $password),
            new Parameter('_method', 'POST')
        ];

        $urls = [];
        $urls[] = new Request($loginUrl, $valuesLogin);
        $this->_client->post($urls);
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
        $parameter["description"] = "Password to Log in";
        $parameter["required"] = true;
        $parameter["name"] = "Password";
        $credentials["password"] = $parameter;

        $parameter = [];
        $parameter["description"] = "API Password ";
        $parameter["required"] = true;
        $parameter["name"] = "API";
        $credentials["apipassword"] = $parameter;

        return $credentials;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $connection = false;
        try {
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
        $merchants = [];

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('api_key', $this->_apiPassword);
        $valuesFromExport[] = new Parameter('limit', 0);

        $urls = [];
        $urls[] = new Request('http://p.wowtrk.com/offers/offers.xml?', $valuesFromExport);
        $exportReport = $this->_exportClient->get($urls);

        $exportData = self::loadXml($exportReport[0]);

        foreach ($exportData->offer as $merchant) {
            $obj = [];
            $obj['cid'] = (int)$merchant->id;
            $obj['name'] = (string)$merchant->name;
            $obj['url'] = (string)$merchant->preview_url;
            $merchants[] = $obj;
        }

        return $merchants;
    }

    /**
     * @param null $exportReport
     * @return SimpleXMLElement
     */
    private function loadXml($exportReport = null)
    {
        $xml = simplexml_load_string($exportReport, null, LIBXML_NOERROR | LIBXML_NOWARNING);
        return $xml;
    }

    /**
     * @param null $merchantList
     * @param DateTime|null $dStartDate
     * @param DateTime|null $dEndDate
     * @return array
     */
    public function getTransactionList($merchantList = null, DateTime $dStartDate = null, DateTime $dEndDate = null)
    {
        $totalTransactions = [];

        $merchantIdList = Utilities::getMerchantIdMapFromMerchantList($merchantList);
        $merchantMap = Utilities::getMerchantNameMapFromMerchantList($merchantList);

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('api_key', $this->_apiPassword);
        $valuesFromExport[] = new Parameter('start_date', $dStartDate->format("Y-m-d"));
        $valuesFromExport[] = new Parameter('end_date', $dEndDate->format("Y-m-d"));
        $valuesFromExport[] = new Parameter('filter[Stat.offer_id]', implode(",", $merchantIdList));

        $urls = [];
        $urls[] = new Request('http://p.wowtrk.com/stats/lead_report.xml?', $valuesFromExport);
        $exportReport = $this->_exportClient->get($urls);

        $exportData = self::loadXml($exportReport[0]);

        foreach ($exportData->stats->stat as $transaction) {
            if (isset($merchantMap[(string)$transaction->offer])) {
                $obj = [];
                $obj['merchantId'] = $merchantMap[(string)$transaction->offer];
                $obj['date'] = (string)$transaction->date_time;
                $obj['status'] = Utilities::STATUS_CONFIRMED;
                $obj['customId'] = (string)$transaction->sub_id;
                $obj['amount'] = Utilities::parseDouble((string)$transaction->payout);
                $obj['commission'] = Utilities::parseDouble((string)$transaction->payout);
                if ($obj['amount'] != 0 || $obj['commission'] != 0) {
                    $totalTransactions[] = $obj;
                }
            }

        }
        return $totalTransactions;
    }
}
