<?php

namespace Oara\Network\Publisher;
use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use function count;
use function preg_match;
use function str_getcsv;

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
 * @category   FashionTraffic
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class FashionTraffic extends Network
{
    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_client = new Access($credentials);

        $user = $credentials['user'];
        $password = $credentials['password'];
        $loginUrl = 'http://system.fashiontraffic.com/';

        $valuesLogin = [
            new Parameter('_method', "POST"),
            new Parameter('data[User][type]', 'affiliate_user'),
            new Parameter('data[User][email]', $user),
            new Parameter('data[User][password]', $password)

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

        return $credentials;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $connection = false;
        $urls = [];
        $urls[] = new Request('http://system.fashiontraffic.com/', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match("/\/logout/", $exportReport[0], $matches)) {
            $connection = true;
        }
        return $connection;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = [];

        $valuesFormExport = [];
        $urls = [];
        $urls[] = new Request('http://system.fashiontraffic.com/stats/ajax_filter_options/Offers', $valuesFormExport);
        $exportReport = $this->_client->post($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//option');
        foreach ($results as $result) {
            $cid = $result->attributes->getNamedItem("value")->nodeValue;
            $name = $result->nodeValue;
            $obj = [];
            $obj['cid'] = $cid;
            $obj['name'] = $name;
            $merchants[] = $obj;
        }
        return $merchants;
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

        $merchantIdList = Utilities::getMerchantIdMapFromMerchantList($merchantList);

        $valuesFormExport = [];
        $urls = [];
        $urls[] = new Request('http://system.fashiontraffic.com/stats/lead_report', $valuesFormExport);
        $exportReport = $this->_client->post($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//input[@name="data[_Token][key]"]');
        foreach ($results as $values) {
            $valuesFormExport[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }
        $results = $xpath->query('//input[@name="data[_Token][fields]"]');
        foreach ($results as $values) {
            $valuesFormExport[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }
        $valuesFormExport[] = new Parameter("_method", 'POST');
        $valuesFormExport[] = new Parameter("data[Report][page]", '');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.offer_id');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.datetime');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.ad_id');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.source');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.affiliate_info1');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.affiliate_info2');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.affiliate_info3');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.affiliate_info4');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.affiliate_info5');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.conversion_payout');
        $valuesFormExport[] = new Parameter("data[Report][fields][]", 'Stat.conversion_status');
        $valuesFormExport[] = new Parameter("data[Report][search][field]", '');
        $valuesFormExport[] = new Parameter("data[Report][search][value]", '');
        $valuesFormExport[] = new Parameter("data[DateRange][timezone]", 'America/New_York');
        $valuesFormExport[] = new Parameter("data[DateRange][preset_date_range]", 'other');
        $valuesFormExport[] = new Parameter("data[DateRange][start_date]", $dStartDate->format("Y-m-d"));
        $valuesFormExport[] = new Parameter("data[DateRange][end_date]", $dEndDate->format("Y-m-d"));

        $urls = [];
        $urls[] = new Request('http://system.fashiontraffic.com/stats/lead_report', $valuesFormExport);
        $exportReport = $this->_client->post($urls);

        $csvUrl = null;
        if (preg_match("/report:(.*).csv/", $exportReport[0], $match)) {
            $csvUrl = "http://system.fashiontraffic.com/stats/conversion_report/report:{$match[1]}.csv";
        }
        $valuesFormExport = [];
        $urls = [];
        $urls[] = new Request($csvUrl, $valuesFormExport);
        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv($exportReport[0], "\n");

        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            if (isset($merchantIdList[(int)$transactionExportArray[0]])) {
                $transaction = [];
                $merchantId = (int)$transactionExportArray[0];
                $transaction['merchantId'] = $merchantId;
                $transaction['date'] = $transactionExportArray[1];

                if ($transactionExportArray[5] != null) {
                    $transaction['custom_id'] = $transactionExportArray[5];
                }

                if ($transactionExportArray[10] == 'approved') {
                    $transaction['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($transactionExportArray[10] == 'rejected') {
                    $transaction['status'] = Utilities::STATUS_DECLINED;
                } else {
                    throw new Exception("Status {$transactionExportArray[10]} unknown");
                }
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[9]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[9]);
                $totalTransactions[] = $transaction;
            }
        }
        return $totalTransactions;

    }

}
