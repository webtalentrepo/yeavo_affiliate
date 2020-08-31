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
use function preg_replace;
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
 * @category   MyPcBackUP
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class MyPcBackUP extends Network
{

    private $_credentials = null;
    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $this->_client = new Access ($credentials);


        $valuesLogin = [
            new Parameter('username', $this->_credentials['user']),
            new Parameter('password', $this->_credentials['password']),
            new Parameter('login', 'Login'),
        ];
        $loginUrl = 'http://affiliates.mypcbackup.com/login';

        $urls = [];
        $urls[] = new Request($loginUrl, $valuesLogin);
        $this->_client->post($urls);

    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        //If not login properly the construct launch an exception
        $connection = true;
        $urls = [];
        $urls[] = new Request('http://affiliates.mypcbackup.com/', []);

        $exportReport = $this->_client->get($urls);
        if (!preg_match("/logout/", $exportReport[0])) {
            $connection = false;
        }
        return $connection;
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
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = [];

        $obj = [];
        $obj['cid'] = "1";
        $obj['name'] = "MyPcBackUp";
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
        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('hop_id', "0");
        $valuesFromExport[] = new Parameter('transaction_id', "");
        $valuesFromExport[] = new Parameter('sales', "1");
        $valuesFromExport[] = new Parameter('refunds', "1");
        $valuesFromExport[] = new Parameter('csv', "Download CSV");
        $valuesFromExport[] = new Parameter('start', $dStartDate->format("m/d/Y"));
        $valuesFromExport[] = new Parameter('end', $dEndDate->format("m/d/Y"));

        $urls[] = new Request('http://affiliates.mypcbackup.com/transactions?', $valuesFromExport);
        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv($exportReport[0], "\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            $transaction = [];
            $transaction['merchantId'] = 1;
            $transaction['uniqueId'] = $transactionExportArray[2];
            $transaction['date'] = $transactionExportArray[0] . " " . $transactionExportArray[1];

            $transaction['amount'] = Utilities::parseDouble($transactionExportArray[5]);
            $transaction['commission'] = Utilities::parseDouble($transactionExportArray[5]);

            if ($transactionExportArray[4] == "Sale") {
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
            } elseif ($transactionExportArray[4] == "Refund") {
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
                $transaction['amount'] = -$transaction['amount'];
                $transaction['commission'] = -$transaction['commission'];
            }
            if ($transactionExportArray[7] != null) {
                $transaction['customId'] = $transactionExportArray[7];
            }
            $totalTransactions[] = $transaction;
        }

        return $totalTransactions;
    }

    /**
     * @return array
     */
    public function getPaymentHistory()
    {
        $paymentHistory = [];

        $urls = [];
        $urls[] = new Request('http://affiliates.mypcbackup.com/paychecks', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " transtable ")]');
        if ($tableList->item(0) != null) {
            $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));
            $num = count($exportData);
            for ($i = 1; $i < $num; $i++) {
                $paymentExportArray = str_getcsv($exportData[$i], ";");
                try {
                    $obj = [];
                    $date = DateTime::createFromFormat("m/d/Y", $paymentExportArray[14]);
                    $date->setTime(0, 0);
                    $obj ['date'] = $date->format("Y-m-d H:i:s");
                    $obj['pid'] = preg_replace('/[^0-9\.,]/', "", $paymentExportArray[14]);
                    $obj['method'] = $paymentExportArray[16];
                    $obj['value'] = Utilities::parseDouble($paymentExportArray[12]);
                    $paymentHistory[] = $obj;

                } catch (Exception $e) {
                    echo "Payment failed\n";
                }
            }
        }
        return $paymentHistory;
    }

}
