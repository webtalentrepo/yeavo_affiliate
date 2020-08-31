<?php

namespace Oara\Network\Publisher;
use DateInterval;
use DateTime;
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
 * @category   PepperJam
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PepperJam extends Network
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

        $loginUrl = 'https://www.pepperjamnetwork.com/login.php';

        $valuesLogin = [
            new Parameter('email', $user),
            new Parameter('passwd', $password),
            new Parameter('hideid', '')
        ];

        $urls = [];
        $urls [] = new Request ($loginUrl, $valuesLogin);
        $this->_client->post($urls);

    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        $connection = false;
        $urls = [];
        $urls[] = new Request('http://www.pepperjamnetwork.com/affiliate/transactionrep.php', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match('/\/logout\.php/', $exportReport[0], $matches)) {
            $connection = true;
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

        $urls = [];
        $urls[] = new Request('http://www.pepperjamnetwork.com/affiliate/program/manage?statuses[]=1&csv=1', []);
        $exportReport = $this->_client->get($urls);

        $merchantList = str_getcsv($exportReport[0], "\n");
        for ($i = 1; $i < count($merchantList); $i++) {
            $merchant = str_getcsv($merchantList[$i], ",");
            $obj = [];
            $obj['cid'] = $merchant[0];
            $obj['name'] = $merchant[1];
            $obj['url'] = $merchant[9];
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
        $valuesFormExport[] = new Parameter('csv', 'csv');
        $valuesFormExport[] = new Parameter('ajax', 'ajax');
        $valuesFormExport[] = new Parameter('type', 'csv');
        $valuesFormExport[] = new Parameter('sortColumn', 'transid');
        $valuesFormExport[] = new Parameter('sortType', 'ASC');
        $valuesFormExport[] = new Parameter('startdate', $dStartDate->format("Y-m-d"));
        $valuesFormExport[] = new Parameter('enddate', $dEndDate->format("Y-m-d"));
        $valuesFormExport[] = new Parameter('programName', 'all');
        $valuesFormExport[] = new Parameter('website', '');
        $valuesFormExport[] = new Parameter('transactionType', '0');
        $valuesFormExport[] = new Parameter('creativeType', 'all');
        $valuesFormExport[] = new Parameter('advancedSubType', '');
        $valuesFormExport[] = new Parameter('saleIdSearch', '');

        $urls = [];
        $urls[] = new Request('http://www.pepperjamnetwork.com/affiliate/report_transaction_detail.php?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv($exportReport[0], "\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            if (isset($merchantIdList[(int)$transactionExportArray[1]])) {
                $transaction = [];
                $merchantId = (int)$transactionExportArray[1];
                $transaction['merchantId'] = $merchantId;
                $transaction['date'] = $transactionExportArray[9];
                $transaction['unique_id'] = $transactionExportArray[0];
                if ($transactionExportArray[4] != null) {
                    $transaction['custom_id'] = $transactionExportArray[4];
                }
                $status = $transactionExportArray[11];
                if ($status == 'Pending' || $status == 'Delayed' || $status == 'Updated Pending Commission') {
                    $transaction['status'] = Utilities::STATUS_PENDING;
                } elseif ($status == 'Locked') {
                    $transaction['status'] = Utilities::STATUS_DECLINED;
                } elseif ($status == 'Paid') {
                    $transaction['status'] = Utilities::STATUS_CONFIRMED;
                } else {
                    throw new Exception("Status {$status} unknown");
                }
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[7]);
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[8]);
                $totalTransactions[] = $transaction;
            }
        }
        return $totalTransactions;
    }

    /**
     * @return array
     */
    public function getPaymentHistory()
    {
        $paymentHistory = [];
        $pointer = new DateTime("2010-01-01");
        $now = new DateTime();
        while ($now->format("Y") >= $pointer->format("Y")) {
            $valuesFormExport = [];
            $valuesFormExport[] = new Parameter('csv', 'csv');
            $valuesFormExport[] = new Parameter('ajax', 'ajax');
            $valuesFormExport[] = new Parameter('type', 'csv');
            $valuesFormExport[] = new Parameter('sortColumn', 'paymentid');
            $valuesFormExport[] = new Parameter('sortType', 'ASC');
            $valuesFormExport[] = new Parameter('startdate', $pointer->format("Y") . "-01-01");
            $valuesFormExport[] = new Parameter('enddate', $pointer->format("Y") . "-12-31");
            $valuesFormExport[] = new Parameter('payid_search', '');

            $urls = [];
            $urls[] = new Request('http://www.pepperjamnetwork.com/affiliate/report_payment_history.php?', $valuesFormExport);
            $exportReport = $this->_client->get($urls);

            $exportData = str_getcsv($exportReport[0], "\n");
            $num = count($exportData);
            for ($i = 1; $i < $num; $i++) {
                $paymentExportArray = str_getcsv($exportData[$i], ",");
                $obj = [];
                $obj['date'] = $paymentExportArray[5];
                $obj['pid'] = $paymentExportArray[0];
                $obj['value'] = Utilities::parseDouble($paymentExportArray[4]);
                $obj['method'] = $paymentExportArray[2];
                $paymentHistory[] = $obj;
            }
            $pointer->add(new DateInterval('P1Y'));
        }

        return $paymentHistory;
    }

    /**
     * @param $paymentId
     * @return array
     */
    public function paymentTransactions($paymentId)
    {
        $transactionList = [];

        $valuesFormExport = [];
        $valuesFormExport[] = new Parameter('csv', 'csv');
        $valuesFormExport[] = new Parameter('ajax', 'ajax');
        $valuesFormExport[] = new Parameter('type', 'csv');
        $valuesFormExport[] = new Parameter('sortColumn', '');
        $valuesFormExport[] = new Parameter('sortType', '');
        $valuesFormExport[] = new Parameter('startdate', '');
        $valuesFormExport[] = new Parameter('enddate', '');
        $valuesFormExport[] = new Parameter('paymentid', $paymentId);

        $urls = [];
        $urls[] = new Request('http://www.pepperjamnetwork.com/affiliate/report_payment_history_detail.php?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);

        $exportData = str_getcsv($exportReport[0], "\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionArray = str_getcsv($exportData[$i], ",");
            $transactionList[] = $transactionArray[1];
        }

        return $transactionList;
    }

}
