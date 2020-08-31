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
 * @category   AffiliateGateway
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class AffiliateGateway extends Network
{
    protected $_extension = null;
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

        $valuesLogin = [
            new Parameter('username', $user),
            new Parameter('password', $password)
        ];
        $loginUrl = "{$this->_extension}/login.html";

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
        $urls[] = new Request("{$this->_extension}/affiliate_home.html", []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " logout ")]');
        if ($results->length > 0) {
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

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('p', "");
        $valuesFromExport[] = new Parameter('time', "1");
        $valuesFromExport[] = new Parameter('changePage', "");
        $valuesFromExport[] = new Parameter('oldColumn', "programmeId");
        $valuesFromExport[] = new Parameter('sortField', "programmeId");
        $valuesFromExport[] = new Parameter('order', "up");
        $valuesFromExport[] = new Parameter('records', "-1");
        $urls = [];
        $urls[] = new Request("{$this->_extension}/affiliate_program_active.html?", $valuesFromExport);
        $exportReport = $this->_client->get($urls);


        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//table[@class="bluetable"]');


        $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));
        $num = count($exportData);
        for ($i = 4; $i < $num; $i++) {
            $merchantExportArray = str_getcsv($exportData[$i], ";");
            if ($merchantExportArray[0] != "No available programs.") {
                $obj = [];
                $obj['cid'] = $merchantExportArray[0];
                $obj['name'] = $merchantExportArray[2];
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

        $merchantNameMap = Utilities::getMerchantNameMapFromMerchantList($merchantList);
        $totalTransactions = [];

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('period', '8');
        $valuesFromExport[] = new Parameter('websiteId', '-1');
        $valuesFromExport[] = new Parameter('merchantId', '-1');
        $valuesFromExport[] = new Parameter('subId', '');
        $valuesFromExport[] = new Parameter('approvalStatus', '-1');
        $valuesFromExport[] = new Parameter('records', '20');
        $valuesFromExport[] = new Parameter('sortField', 'purchDate');
        $valuesFromExport[] = new Parameter('time', '1');
        $valuesFromExport[] = new Parameter('p', '1');
        $valuesFromExport[] = new Parameter('changePage', '1');
        $valuesFromExport[] = new Parameter('oldColumn', 'purchDate');
        $valuesFromExport[] = new Parameter('order', 'down');
        $valuesFromExport[] = new Parameter('mId', '-1');
        $valuesFromExport[] = new Parameter('submittedSubId', '');
        $valuesFromExport[] = new Parameter('exportType', 'csv');
        $valuesFromExport[] = new Parameter('reportTitle', 'report');

        $valuesFromExport[] = new Parameter('startDate', $dStartDate->format("d/m/Y"));
        $valuesFromExport[] = new Parameter('endDate', $dEndDate->format("d/m/Y"));

        $urls = [];
        $urls[] = new Request("{$this->_extension}/affiliate_statistic_transaction.html?", $valuesFromExport);


        $exportReport = $this->_client->get($urls);
        $exportData = str_getcsv($exportReport[0], "\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            if (isset($merchantNameMap[$transactionExportArray[1]])) {
                $merchantId = $merchantNameMap[$transactionExportArray[1]];

                $transaction = [];
                $transaction['merchantId'] = $merchantId;
                $transactionDate = DateTime::createFromFormat("d/m/Y H:i:s", $transactionExportArray[4]);
                $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction['unique_id'] = $transactionExportArray[0];

                if ($transactionExportArray[11] != null) {
                    $transaction['custom_id'] = $transactionExportArray[11];
                }

                if ($transactionExportArray[12] == "Approved" || $transactionExportArray[12] == "Approve") {
                    $transaction['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($transactionExportArray[12] == "Pending") {
                    $transaction['status'] = Utilities::STATUS_PENDING;
                } elseif ($transactionExportArray[12] == "Declined" || $transactionExportArray[12] == "Rejected") {
                    $transaction['status'] = Utilities::STATUS_DECLINED;
                } else {
                    throw new Exception ("No Status found " . $transactionExportArray[12]);
                }
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[7]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[9]);
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

        $urls = [];
        $urls[] = new Request("{$this->_extension}/affiliate_invoice.html?", []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//table[@class="bluetable"]');
        $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));
        $num = count($exportData);
        for ($i = 4; $i < $num; $i++) {
            $paymentExportArray = str_getcsv($exportData[$i], ";");
            if (count($paymentExportArray) > 7) {
                $obj = [];
                $date = DateTime::createFromFormat("d/m/Y", $paymentExportArray[1]);
                $obj['date'] = $date->format("Y-m-d H:i:s");
                $obj['pid'] = preg_replace('/[^0-9]/', "", $paymentExportArray[0]);
                $obj['method'] = 'BACS';
                $obj['value'] = Utilities::parseDouble($paymentExportArray[4]);
                $paymentHistory[] = $obj;
            }

        }
        return $paymentHistory;
    }

}
