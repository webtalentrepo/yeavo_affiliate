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
use function str_getcsv;
use function utf8_decode;

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
 * @category   TotalVPN
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class TotalVPN extends Network
{
    private $_credentials = null;
    private $_client = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $this->_client = new Access($this->_credentials);

        $loginUrl = 'http://affiliates.totalvpn.com/login';
        $urls = [];
        $urls[] = new Request($loginUrl, []);
        $exportReport = $this->_client->get($urls);
        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//form[@action="/login"]/descendant::input[@type="hidden"]');
        $valuesLogin = [
            new Parameter('username', $credentials['user']),
            new Parameter('password', $credentials['password']),
        ];
        foreach ($hidden as $values) {
            $valuesLogin[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }

        $loginUrl = 'http://affiliates.totalvpn.com/login';
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
        //If not login properly the construct launch an exception
        $connection = true;
        $urls = [];
        $urls[] = new Request('http://affiliates.totalvpn.com', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//a[@href="/logout"]');
        if ($results->length == 0) {
            $connection = false;
        }
        return $connection;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = [];

        $obj = [];
        $obj['cid'] = "1";
        $obj['name'] = "TotalVPN";
        $obj['url'] = "http://affiliates.totalvpn.com";
        $merchants[] = $obj;

        return $merchants;
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


        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('dateStart', $dStartDate->format("Y-m-d"));
        $valuesFromExport[] = new Parameter('dateEnd', $dEndDate->format("Y-m-d"));
        $valuesFromExport[] = new Parameter('csv', '1');
        $urls = [];
        $urls[] = new Request('http://affiliates.totalvpn.com/reporting/view/date?', $valuesFromExport);

        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv(utf8_decode($exportReport[0]), "\n");
        $num = count($exportData);
        $headerArray = str_getcsv($exportData[0], ",");
        $headerMap = [];
        for ($j = 0; $j < count($headerArray); $j++) {
            $headerMap[$headerArray[$j]] = $j;
        }

        for ($j = 1; $j < $num; $j++) {
            $transactionExportArray = str_getcsv($exportData[$j], ",");

            $transaction = [];
            $transaction['merchantId'] = 1;
            $transaction['date'] = $transactionExportArray[$headerMap["Date.Date"]] . " 00:00:00";
            $total = Utilities::parseDouble($transactionExportArray[$headerMap["Commission.Commission"]]) - Utilities::parseDouble($transactionExportArray[$headerMap["Commission.Reversed"]]);
            $transaction['amount'] = $total;
            $transaction['commission'] = $total;
            $transaction['status'] = Utilities::STATUS_CONFIRMED;
            $totalTransactions[] = $transaction;
        }


        return $totalTransactions;
    }
}
