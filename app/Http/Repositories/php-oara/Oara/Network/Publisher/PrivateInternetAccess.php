<?php

namespace Oara\Network\Publisher;
use DateInterval;
use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;

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
 * @category   Webepartners
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PrivateInternetAccess extends Network
{

    private $_client = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {
        $user = $credentials['user'];
        $password = $credentials['password'];
        $this->_client = new Access($credentials);


        $url = "https://www.privateinternetaccess.com/affiliates/sign_in";
        $valuesLogin = [
            new Parameter('affiliate[email]', $user),
            new Parameter('affiliate[password]', $password),
        ];
        $urls = [];
        $urls[] = new Request($url, $valuesLogin);
        $exportReport = $this->_client->post($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//input[@type="hidden"]');
        $hiddenValue = null;
        foreach ($results as $result) {
            $name = $result->attributes->getNamedItem("name")->nodeValue;
            if ($name == "authenticity_token") {
                $hiddenValue = $result->attributes->getNamedItem("value")->nodeValue;
            }
        }
        if ($hiddenValue == null) {
            throw new Exception("hidden value not found");
        }

        $valuesLogin = [
            new Parameter('authenticity_token', $hiddenValue),
            new Parameter('affiliate[email]', $user),
            new Parameter('affiliate[password]', $password),
            new Parameter('utf8', '&#x2713;'),
            new Parameter('commit', 'Login'),
            new Parameter('affiliate[remember_me]', '0'),
        ];

        $urls = [];
        $urls[] = new Request($url, $valuesLogin);
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

        $connection = true;
        $valuesFormExport = [];
        $urls = [];
        $urls[] = new Request('https://www.privateinternetaccess.com/affiliates/affiliate_dashboard', $valuesFormExport);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " login ")]');

        if ($results->length > 0) {
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
        $obj['name'] = "Private Internet Access";
        $obj['url'] = "https://www.privateinternetaccess.com/affiliates";
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
        $amountDays = $dStartDate->diff($dEndDate)->days;
        $auxDate = clone $dStartDate;
        for ($j = 0; $j < $amountDays; $j++) {

            $valuesFormExport = [];
            $valuesFormExport[] = new Parameter('utf', '✓');
            $valuesFormExport[] = new Parameter('start_date', $auxDate->format("d M Y"));
            $valuesFormExport[] = new Parameter('end_date', $auxDate->format("d M Y"));

            $urls = [];
            $urls[] = new Request('https://www.privateinternetaccess.com/affiliates/affiliate_dashboard?', $valuesFormExport);
            $exportReport = $this->_client->get($urls);

            $doc = new DOMDocument();
            @$doc->loadHTML($exportReport[0]);
            $xpath = new DOMXPath($doc);
            $results = $xpath->query('//h4[contains(., " Grand total")]/following-sibling::table/tbody/tr/td');
            if ($results->length > 0) {

                $exportData = $results->item(1);
                $commission = Utilities::parseDouble(substr($exportData->nodeValue, 1));

                $transaction = [];
                $transaction['merchantId'] = "1";
                $transaction['date'] = $auxDate->format("Y-m-d H:i:s");
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
                $transaction['amount'] = $commission;
                $transaction['commission'] = $commission;
                $totalTransactions[] = $transaction;
            }
            $auxDate->add(new DateInterval('P1D'));
        }
        return $totalTransactions;
    }


}
