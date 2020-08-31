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
use function is_numeric;
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
 * API Class
 *
 * @author     Carlos Morillo Merino
 * @category   Afiliant
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Afiliant extends Network
{


    /**
     * @var null
     */
    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {

        $user = $credentials['user'];
        $password = $credentials['password'];

        $this->_client = new Access($credentials);

        $loginUrl = 'https://ssl.afiliant.com/publisher/index.php?a=auth';
        $valuesLogin = [
            new Parameter('login', $user),
            new Parameter('password', $password),
            new Parameter('submit', "")
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
        $urls[] = new Request('http://www.afiliant.com/publisher/index.php', []);
        $exportReport = $this->_client->get($urls);
        if (!preg_match("/index.php?a=logout/", $exportReport[0], $matches)) {
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

        $valuesFromExport = [
            new Parameter('c', 'stats'),
            new Parameter('a', 'listMonth')
        ];
        $urls = [];
        $urls[] = new Request('http://www.afiliant.com/publisher/index.php?', $valuesFromExport);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " id_shop ")]');

        $merchantLines = $results->item(0)->childNodes;
        for ($i = 0; $i < $merchantLines->length; $i++) {
            $cid = $merchantLines->item($i)->attributes->getNamedItem("value")->nodeValue;
            if (is_numeric($cid)) {
                $name = $merchantLines->item($i)->nodeValue;
                $obj = [];
                $obj['cid'] = $cid;
                $obj['name'] = $name;
                $merchants[] = $obj;
            }
        }

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
        $merchantMap = Utilities::getMerchantNameMapFromMerchantList($merchantList);

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('c', 'stats');
        $valuesFromExport[] = new Parameter('id_shop', '');
        $valuesFromExport[] = new Parameter('a', 'listMonthDayOrder');
        $valuesFromExport[] = new Parameter('month', $dEndDate->fromat("Y-m"));
        $valuesFromExport[] = new Parameter('export', 'csv');

        $urls = [];
        $urls[] = new Request('http://www.afiliant.com/publisher/index.php?', $valuesFromExport);

        $exportData = null;
        try {
            $exportReport = $this->_client->get($urls);
            $exportData = str_getcsv($exportReport[0], "\r\n");
        } catch (Exception $e) {
            echo "No data \n";
        }
        if ($exportData != null) {
            $num = count($exportData);
            for ($i = 0; $i < $num; $i++) {
                $transactionExportArray = str_getcsv($exportData[$i], ";");

                if (isset($merchantMap[$transactionExportArray[1]])) {
                    $transaction = [];
                    $merchantId = (int)$merchantMap[$transactionExportArray[1]];
                    $transaction['merchantId'] = $merchantId;
                    $transaction['date'] = $transactionExportArray[0] . " 00:00:00";
                    $transaction['unique_id'] = $transactionExportArray[3];

                    if (isset($transactionExportArray[8]) && $transactionExportArray[8] != null) {
                        $transaction['custom_id'] = $transactionExportArray[8];
                    }

                    if ($transactionExportArray[6] == 'zaakceptowana') {
                        $transaction['status'] = Utilities::STATUS_CONFIRMED;
                    } elseif ($transactionExportArray[6] == 'oczekuje') {
                        $transaction['status'] = Utilities::STATUS_PENDING;
                    } elseif ($transactionExportArray[6] == 'odrzucona') {
                        $transaction['status'] = Utilities::STATUS_DECLINED;
                    }
                    $transaction['amount'] = Utilities::parseDouble($transactionExportArray[4]);
                    $transaction['commission'] = Utilities::parseDouble($transactionExportArray[5]);
                    $totalTransactions[] = $transaction;
                }
            }
        }

        return $totalTransactions;
    }
}
