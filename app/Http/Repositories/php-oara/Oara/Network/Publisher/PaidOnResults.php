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
 * @category   Por
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PaidOnResults extends Network
{

    private $_client = null;
    private $_sessionId = null;

    /**
     * @param $credentials
     * @throws Exception
     * @throws Exception
     * @throws \Oara\Curl\Exception
     */
    public function login($credentials)
    {
        $this->_user = $credentials['user'];
        $password = $credentials['password'];
        $this->_apiPassword = $credentials['apipassword'];
        $this->_client = new Access ($credentials);

        $loginUrl = 'https://www.paidonresults.com/login/';
        $valuesLogin = [
            new Parameter('username', $this->_user),
            new Parameter('password', $password)
        ];

        $urls = [];
        $urls[] = new Request($loginUrl, $valuesLogin);
        $this->_client->post($urls);

        $urls = [];
        $urls[] = new Request('http://affiliate.paidonresults.com/cgi-bin/home.pl', []);
        $exportReport = $this->_client->post($urls);
        if (!preg_match('/http\:\/\/affiliate\.paidonresults\.com\/cgi\-bin\/logout\.pl/', $exportReport[0], $matches)) {
            throw new Exception("Error on login");
        }

        if (preg_match("/URL=(.*)\"/", $exportReport[0], $matches)) {
            $urls = [];
            $urls[] = new Request($matches[1], []);
            $this->_client->get($urls);
        }
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
     * Check the connection
     */
    public function checkConnection()
    {
        $connection = true;
        return $connection;
    }

    /**
     * (non-PHPdoc)
     * @see library/Oara/Network/Base#getMerchantList()
     */
    public function getMerchantList()
    {
        $merchants = [];

        $valuesFormExport = [
            new Parameter('apikey', $this->_apiPassword),
            new Parameter('Format', 'CSV'),
            new Parameter('FieldSeparator', 'comma'),
            new Parameter('AffiliateID', $this->_user),
            new Parameter('MerchantCategories', 'ALL'),
            new Parameter('Fields', 'MerchantID,MerchantName,MerchantURL'),
            new Parameter('JoinedMerchants', 'YES'),
            new Parameter('MerchantsNotJoined', 'NO'),
        ];

        $urls = [];
        $urls[] = new Request('http://affiliate.paidonresults.com/api/merchant-directory?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv($exportReport[0], "\r\n");
        $exportData = preg_replace("/\n/", "", $exportData);
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $merchantExportArray = str_getcsv($exportData[$i], ",");
            $obj = [];
            $obj['cid'] = $merchantExportArray[0];
            $obj['name'] = $merchantExportArray[1];
            $obj['url'] = $merchantExportArray[2];
            $merchants[] = $obj;
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
        $merchantIdList = Utilities::getMerchantIdMapFromMerchantList($merchantList);


        $urls = [];
        $valuesFormExport = [
            new Parameter('apikey', $this->_apiPassword),
            new Parameter('Format', 'CSV'),
            new Parameter('FieldSeparator', 'comma'),
            new Parameter('Fields', 'MerchantID,OrderDate,NetworkOrderID,CustomTrackingID,OrderValue,AffiliateCommission,TransactionType,PaidtoAffiliate,DatePaidToAffiliate'),
            new Parameter('AffiliateID', $this->_user),
            new Parameter('DateFormat', 'DD/MM/YYYY+HH:MN:SS'),
            new Parameter('PendingSales', 'YES'),
            new Parameter('ValidatedSales', 'YES'),
            new Parameter('VoidSales', 'YES'),
            new Parameter('GetNewSales', 'YES')
        ];
        $valuesFormExport[] = new Parameter('DateFrom', $dStartDate->format("Y-m-d"));
        $valuesFormExport[] = new Parameter('DateTo', $dEndDate->format("Y-m-d"));
        $urls[] = new Request('http://affiliate.paidonresults.com/api/transactions?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);

        $exportData = str_getcsv($exportReport[0], "\r\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {

            $exportData[$i] = preg_replace("/\n/", "", $exportData[$i]);
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            if (isset($merchantIdList[$transactionExportArray[0]])) {
                $transaction = [];
                $transaction['merchantId'] = $transactionExportArray[0];
                $transactionDate = DateTime::createFromFormat("d/m/Y H:i:s", $transactionExportArray[1]);
                $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction['unique_id'] = $transactionExportArray[2];
                if ($transactionExportArray[3] != null) {
                    $transaction['custom_id'] = $transactionExportArray[3];
                }
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[4]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[5]);

                if ($transactionExportArray[6] == 'VALIDATED') {
                    $transaction['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($transactionExportArray[6] == 'PENDING') {
                    $transaction['status'] = Utilities::STATUS_PENDING;
                } elseif ($transactionExportArray[6] == 'VOID') {
                    $transaction['status'] = Utilities::STATUS_DECLINED;
                }

                $totalTransactions[] = $transaction;
            }
        }

        return $totalTransactions;

    }
}
