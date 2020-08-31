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
use function explode;
use function preg_match;

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
 * API Class
 *
 * @author Carlos Morillo Merino
 * @category Shuttlefare
 * @copyright Fubra Limited
 * @version Release: 01.00
 *
 */
class Shuttlefare extends Network
{

    /**
     * @var null
     */
    private $_client = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {

        $user = $credentials ['user'];
        $password = $credentials ['password'];
        $this->_client = new Access ($credentials);

        $loginUrl = 'http://affiliates.shuttlefare.com/users/sign_in';
        $urls = [];
        $urls [] = new Request ($loginUrl, []);
        $exportReport = $this->_client->get($urls);
        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//input[@type="hidden"]');
        $valuesLogin = [
            new Parameter ('user[email]', $user),
            new Parameter ('user[password]', $password),
            new Parameter ('user[remember_me]', '0'),
            new Parameter ('commit', 'Sign in')
        ];
        foreach ($results as $values) {
            $valuesLogin[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }
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
        $urls[] = new Request('http://affiliates.shuttlefare.com/partners', []);
        $exportReport = $this->_client->get($urls);
        if (preg_match("/logout/", $exportReport[0], $matches)) {
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

        $obj = [];
        $obj['cid'] = 1;
        $obj['name'] = 'Shuttlefare';
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

        $valuesFromExport = [
            new Parameter('payment[from]', $dStartDate->format("m/d/Y")),
            new Parameter('payment[to]', $dEndDate->format("m/d/Y")),
        ];

        $urls = [];
        $urls[] = new Request('http://affiliates.shuttlefare.com/partners/payments/report.csv?', $valuesFromExport);
        try {
            $exportReport = $this->_client->get($urls);
        } catch (Exception $e) {
            return $totalTransactions;
        }

        if (!preg_match("/No transaction in given date range/", $exportReport[0]) && $exportReport[0]) {
            $exportData = explode("\n", $exportReport[0]);
            $num = count($exportData);
            for ($i = 0; $i < $num - 1; $i++) {
                $transactionExportArray = explode(",", $exportData [$i]);
                $transaction = [];
                $transaction ['merchantId'] = 1;
                $transaction ['unique_id'] = $transactionExportArray [0];
                $transactionDate = DateTime::createFromFormat("m/d/Y H:i:s", $transactionExportArray [7] . " 00:00:00");
                $transaction ['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction ['status'] = Utilities::STATUS_CONFIRMED;
                $transaction ['amount'] = Utilities::parseDouble($transactionExportArray [2]);
                $transaction ['commission'] = Utilities::parseDouble($transactionExportArray [3]);
                $totalTransactions [] = $transaction;
            }
        }

        return $totalTransactions;
    }
}
