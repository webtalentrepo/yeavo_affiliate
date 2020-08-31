<?php

namespace Oara\Network\Publisher;
use DateInterval;
use DateTime;
use DOMDocument;
use DOMXPath;
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
 * @category   PayMode
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PayMode extends Network
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
            new Parameter('username', $user),
            new Parameter('password', $password),
            new Parameter('Enter', 'Enter')
        ];
        $loginUrl = 'https://secure.paymode.com/paymode/do-login.jsp?';
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
        $urls[] = new Request('https://secure.paymode.com/paymode/home.jsp?', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match('/class="logout"/', $exportReport[0], $matches)) {

            $urls = [];
            $urls[] = new Request('https://secure.paymode.com/paymode/reports-pre_commission_history.jsp?', []);
            $exportReport = $this->_client->get($urls);
            $doc = new DOMDocument();
            @$doc->loadHTML($exportReport[0]);
            $xpath = new DOMXPath($doc);
            $results = $xpath->query('//input[@type="checkbox"]');
            $agentNumber = [];
            foreach ($results as $result) {
                $agentNumber[] = $result->getAttribute("id");
            }
            $this->_agentNumber = $agentNumber;
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
        $obj['name'] = "Sixt";
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
        $urls = [];
        $urls[] = new Request('https://secure.paymode.com/paymode/reports-baiv2.jsp?', []);
        $exportReport = $this->_client->get($urls);
        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//input[@type="hidden"]');
        foreach ($results as $hidden) {
            $name = $hidden->getAttribute("name");
            $value = $hidden->getAttribute("value");
            $valuesFromExport[] = new Parameter($name, $value);
        }
        $valuesFromExport[] = new Parameter('dataSource', '1');
        $valuesFromExport[] = new Parameter('RA:reports-baiv2.jspCHOOSE', '620541800');
        $valuesFromExport[] = new Parameter('reportFormat', 'csv');
        $valuesFromExport[] = new Parameter('includeCurrencyCodeColumn', 'on');
        $valuesFromExport[] = new Parameter('remitTypeCode', '');
        $valuesFromExport[] = new Parameter('PAYMENT_CURRENCY_TYPE', 'CREDIT');
        $valuesFromExport[] = new Parameter('PAYMENT_CURRENCY_TYPE', 'INSTRUCTION');
        $valuesFromExport[] = new Parameter('subSiteExtID', '');
        $valuesFromExport[] = new Parameter('ediProvider835Version', '5010');
        $valuesFromExport[] = new Parameter('tooManyRowsCheck', 'true');

        $urls = [];
        $amountDays = $dStartDate->diff($dEndDate)->days;
        $auxDate = clone $dStartDate;
        for ($j = 0; $j < $amountDays; $j++) {
            $valuesFromExportTemp = Utilities::cloneArray($valuesFromExport);
            $valuesFromExportTemp[] = new Parameter('date', $auxDate->format("m/d/Y"));
            $urls[] = new Request('https://secure.paymode.com/paymode/reports-do_csv.jsp?closeJQS=true?', $valuesFromExportTemp);
            $auxDate->add(new DateInterval('P1D'));
        }

        $exportReport = $this->_client->get($urls);
        $transactionCounter = 0;
        $valueCounter = 0;
        $commissionCounter = 0;
        $j = 0;
        foreach ($exportReport as $report) {
            if (!preg_match("/logout.jsp/", $report)) {
                $exportReportData = str_getcsv($report, "\n");
                $num = count($exportReportData);
                for ($i = 1; $i < $num; $i++) {
                    $transactionArray = str_getcsv($exportReportData[$i], ",");
                    if (count($transactionArray) == 30 && $transactionArray[0] == 'D' && $transactionArray[1] == null) {
                        $transactionCounter++;
                        $valueCounter += Utilities::parseDouble($transactionArray[24]);
                        $commissionCounter += Utilities::parseDouble($transactionArray[28]);
                    }
                }
            }
            $j++;
        }

        if ($transactionCounter > 0) {
            $auxDate = clone $dStartDate;
            for ($i = 0; $i < $amountDays; $i++) {
                $transaction = [];
                $transaction['merchantId'] = 1;
                $transaction['status'] = Utilities::STATUS_PAID;
                $transaction['date'] = $auxDate->format("Y-m-d H:i:s");
                $transaction['amount'] = Utilities::parseDouble($valueCounter / $amountDays);
                $transaction['commission'] = Utilities::parseDouble($commissionCounter / $amountDays);
                $totalTransactions[] = $transaction;
                $auxDate->add(new DateInterval('P1D'));
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

        $startDate = new DateTime("2012-01-01");
        $endDate = new DateTime();

        $amountMonths = $startDate->diff($endDate)->months;
        $auxDate = clone $startDate;

        for ($j = 0; $j < $amountMonths; $j++) {
            $monthStartDate = clone $auxDate;
            $monthEndDate = null;

            $monthEndDate = clone $auxDate;
            $monthEndDate->add(new DateInterval('P1M'));
            $monthEndDate->sub(new DateInterval('P1D'));
            $monthEndDate->setTime(23, 59, 59);

            $valuesFromExport = [];
            $valuesFromExport[] = new Parameter('Begin_Date', $monthStartDate->format("m/d/Y"));
            $valuesFromExport[] = new Parameter('End_Date', $monthEndDate->format("m/d/Y"));
            $valuesFromExport[] = new Parameter('cd', "c");
            $valuesFromExport[] = new Parameter('disb', "false");
            $valuesFromExport[] = new Parameter('coll', "true");
            $valuesFromExport[] = new Parameter('transactionID', "");
            $valuesFromExport[] = new Parameter('Begin_DatePN', "");
            $valuesFromExport[] = new Parameter('Begin_DateCN', "");
            $valuesFromExport[] = new Parameter('End_DatePN', "");
            $valuesFromExport[] = new Parameter('End_DateCN', "");
            $valuesFromExport[] = new Parameter('disbAcctIDRef', "");
            $valuesFromExport[] = new Parameter('checkNumberID', "");
            $valuesFromExport[] = new Parameter('paymentNum', "");
            $valuesFromExport[] = new Parameter('sel_type', "OTH");
            $valuesFromExport[] = new Parameter('payStatusCat', "ALL_STATUSES");
            $valuesFromExport[] = new Parameter('amount', "");
            $valuesFromExport[] = new Parameter('aggregatedCreditAmount', "");
            $valuesFromExport[] = new Parameter('disbSiteIDManual', "");
            $valuesFromExport[] = new Parameter('collSiteIDManual', "");
            $valuesFromExport[] = new Parameter('agencyid', "");
            $valuesFromExport[] = new Parameter('collbankAccount', "");
            $valuesFromExport[] = new Parameter('remitInvoice', "");
            $valuesFromExport[] = new Parameter('remitAccount', "");
            $valuesFromExport[] = new Parameter('remitCustAccount', "");
            $valuesFromExport[] = new Parameter('remitCustName', "");
            $valuesFromExport[] = new Parameter('remitVendorNumber', "");
            $valuesFromExport[] = new Parameter('remitVendorName', "");

            $urls = [];
            $urls[] = new Request('https://secure.paymode.com/paymode/payment-DB-search.jsp?dataSource=1', $valuesFromExport);
            $exportReport = $this->_client->post($urls);


            if (!preg_match("/No payments were found/", $exportReport[0])) {

                $doc = new DOMDocument();
                @$doc->loadHTML($exportReport[0]);
                $xpath = new DOMXPath($doc);
                $results = $xpath->query('//form[@name="transform"] table');
                if (count($results) > 0) {
                    $tableCsv = Utilities::htmlToCsv(Utilities::DOMinnerHTML($results->item(0)));
                    $payment = [];
                    $paymentArray = str_getcsv($tableCsv[4], ";");
                    $payment['pid'] = $paymentArray[1];

                    $dateResult = $xpath->query('//form[@name="collForm"] table');
                    if (count($dateResult) > 0) {
                        $dateCsv = Utilities::htmlToCsv(Utilities::DOMinnerHTML($dateResult->item(0)));
                        $dateArray = str_getcsv($dateCsv[2], ";");
                        $paymentDate = DateTime::createFromFormat("d-M-Y", $dateArray [1]);
                        $payment['date'] = $paymentDate->format("Y-m-d H:i:s");
                        $paymentArray = str_getcsv($tableCsv[3], ";");
                        $payment['value'] = Utilities::parseDouble($paymentArray[3]);
                        $payment['method'] = "BACS";
                        $paymentHistory[] = $payment;
                    }

                } else {
                    $results = $xpath->query('//table[@cellpadding="2"]');
                    foreach ($results as $table) {

                        $tableCsv = Utilities::htmlToCsv(Utilities::DOMinnerHTML($table));
                        $num = count($tableCsv);
                        for ($i = 1; $i < $num; $i++) {
                            $payment = [];
                            $paymentArray = str_getcsv($tableCsv[$i], ";");
                            $payment['pid'] = $paymentArray[0];
                            $paymentDate = DateTime::createFromFormat("m/d/Y", $paymentArray [3]);
                            $payment['date'] = $paymentDate->format("Y-m-d H:i:s");
                            $payment['value'] = Utilities::parseDouble($paymentArray[9]);
                            $payment['method'] = "BACS";
                            $paymentHistory[] = $payment;
                        }
                    }
                }
            }
            $auxDate->add(new DateInterval('P1M'));
        }
        return $paymentHistory;
    }
}
