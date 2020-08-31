<?php

namespace Oara\Network\Publisher;
use DateTime;
use DOMDocument;
use DOMXPath;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use function array_merge;
use function count;
use function preg_match;
use function preg_replace;
use function str_getcsv;
use function trim;

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
 * @category   CgtAffiliate
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class CgtAffiliate extends Network
{

    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $user = $credentials['user'];
        $password = $credentials['password'];
        $this->_client = new Access($credentials);

        $valuesLogin = [
            new Parameter('userid', $user),
            new Parameter('password', $password),
        ];
        $loginUrl = 'http://www.cgtaffiliate.com/idevaffiliate/login.php';

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
        $connection = false;
        $urls = [];
        $urls[] = new Request('http://www.cgtaffiliate.com/idevaffiliate/account.php', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match("/Logout/", $exportReport[0])) {
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
        $obj['name'] = "Custom Greek Threads";
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
        $urls = [];
        $urls[] = new Request('http://www.cgtaffiliate.com/idevaffiliate/account.php?page=4&report=1', []);
        $exportReport = $this->_client->get($urls);
        $totalTransactions = array_merge($totalTransactions, self::readTransactions($exportReport[0]));
        $urls = [];
        $urls[] = new Request('http://www.cgtaffiliate.com/idevaffiliate/account.php?page=4&report=3', []);
        $exportReport = $this->_client->get($urls);
        $totalTransactions = array_merge($totalTransactions, self::readTransactions($exportReport[0]));
        $urls = [];
        $urls[] = new Request('http://www.cgtaffiliate.com/idevaffiliate/account.php?page=4&report=4', []);
        $exportReport = $this->_client->get($urls);
        $totalTransactions = array_merge($totalTransactions, self::readTransactions($exportReport[0]));

        return $totalTransactions;
    }

    private function readTransactions($html)
    {
        $totalTransactions = [];

        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//table[@bgcolor="#003366"][@align="center"][@width="100%"]');
        $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));
        $num = count($exportData);
        for ($i = 3; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ";");
            $transaction = [];
            $transaction['merchantId'] = 1;
            $transaction['date'] = preg_replace("/[^0-9\-]/", "", $transactionExportArray[0]) . " 00:00:00";
            $transactionExportArray[1] = trim($transactionExportArray[1]);
            if (preg_match("/Paid/", $transactionExportArray[1])) {
                $transaction['status'] = Utilities::STATUS_PAID;
            } elseif (preg_match("/Pending/", $transactionExportArray[1])) {
                $transaction['status'] = Utilities::STATUS_PENDING;
            } elseif (preg_match("/Approved/", $transactionExportArray[1])) {
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
            }
            $transaction['amount'] = Utilities::parseDouble($transactionExportArray[2]);
            $transaction['commission'] = Utilities::parseDouble($transactionExportArray[2]);
            $totalTransactions[] = $transaction;
        }
        return $totalTransactions;
    }

}
