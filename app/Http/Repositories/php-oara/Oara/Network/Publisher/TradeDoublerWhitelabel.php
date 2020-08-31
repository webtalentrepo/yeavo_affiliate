<?php

namespace Oara\Network\Publisher;
use DateTime;
use DOMDocument;
use Exception;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use Zend\Dom\Query;
use ZIPARCHIVE;
use function copy;
use function count;
use function dirname;
use function explode;
use function file_get_contents;
use function in_array;
use function is_numeric;
use function libxml_use_internal_errors;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function realpath;
use function str_getcsv;
use function str_replace;
use function substr;
use function trim;
use function unlink;

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
 * @category   Td
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class TradeDoublerWhitelabel extends Network
{

    protected $_sitesAllowed = [];
    protected $_client = null;
    protected $_dateFormat = null;

    public function login($credentials)
    {

        $this->_credentials = $credentials;
        $this->_client = new Access($credentials);

        $user = $this->_credentials['user'];
        $password = $this->_credentials['password'];
        $loginUrl = 'http://publisher.tradedoubler.com/pan/login';

        $valuesLogin = [
            new Parameter('j_username', $user),
            new Parameter('j_password', $password)
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
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/aReport3Selection.action?reportName=aAffiliateProgramOverviewReport', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match('/\(([a-zA-Z]{0,4}[\/\.-][a-zA-Z]{0,4}[\/\.-][a-zA-Z]{0,4})\)/', $exportReport[0], $match)) {
            $this->_dateFormat = $match[1];
        }


        if ($this->_dateFormat != null) {
            $connection = true;
        }
        return $connection;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchantReportList = self::getMerchantReportList();
        $merchants = [];
        foreach ($merchantReportList as $key => $value) {
            $obj = [];
            $obj['cid'] = $key;
            $obj['name'] = $value;
            $merchants[] = $obj;
        }

        return $merchants;
    }

    /**
     * It returns an array with the different merchants
     * @return array
     */
    private function getMerchantReportList()
    {

        $valuesFormExport = [
            new Parameter('reportName', 'aAffiliateMyProgramsReport'),
            new Parameter('tabMenuName', ''),
            new Parameter('isPostBack', ''),
            new Parameter('showAdvanced', 'true'),
            new Parameter('showFavorite', 'false'),
            new Parameter('run_as_organization_id', ''),
            new Parameter('minRelativeIntervalStartTime', '0'),
            new Parameter('maxIntervalSize', '0'),
            new Parameter('interval', 'MONTHS'),
            new Parameter('reportPrograms', ''),
            new Parameter('reportTitleTextKey', 'REPORT3_SERVICE_REPORTS_AAFFILIATEMYPROGRAMSREPORT_TITLE'),
            new Parameter('setColumns', 'true'),
            new Parameter('latestDayToExecute', '0'),
            new Parameter('affiliateId', ''),
            new Parameter('includeWarningColumn', 'true'),
            new Parameter('sortBy', 'orderDefault'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'programId'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'affiliateId'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'applicationDate'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'status'),
            new Parameter('autoCheckbox', 'useMetricColumn'),
            new Parameter('customKeyMetricCount', '0'),
            new Parameter('metric1.name', ''),
            new Parameter('metric1.midFactor', ''),
            new Parameter('metric1.midOperator', '/'),
            new Parameter('metric1.columnName1', 'programId'),
            new Parameter('metric1.operator1', '/'),
            new Parameter('metric1.columnName2', 'programId'),
            new Parameter('metric1.lastOperator', '/'),
            new Parameter('metric1.factor', ''),
            new Parameter('metric1.summaryType', 'NONE'),
            new Parameter('format', 'CSV'),
            new Parameter('separator', ','),
            new Parameter('dateType', '0'),
            new Parameter('favoriteId', ''),
            new Parameter('favoriteName', ''),
            new Parameter('favoriteDescription', ''),
            new Parameter('programAffiliateStatusId', '3')
        ];
        $urls = [];
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/aReport3Internal.action?', $valuesFormExport);
        $exportReport = $this->_client->post($urls);
        $exportReport[0] = self::checkReportError($exportReport[0], $urls[0]);
        $merchantReportList = self::getExportMerchantReport($exportReport[0]);

        $valuesFormExport = [
            new Parameter('reportName', 'aAffiliateMyProgramsReport'),
            new Parameter('tabMenuName', ''),
            new Parameter('isPostBack', ''),
            new Parameter('showAdvanced', 'true'),
            new Parameter('showFavorite', 'false'),
            new Parameter('run_as_organization_id', ''),
            new Parameter('minRelativeIntervalStartTime', '0'),
            new Parameter('maxIntervalSize', '0'),
            new Parameter('interval', 'MONTHS'),
            new Parameter('reportPrograms', ''),
            new Parameter('reportTitleTextKey', 'REPORT3_SERVICE_REPORTS_AAFFILIATEMYPROGRAMSREPORT_TITLE'),
            new Parameter('setColumns', 'true'),
            new Parameter('latestDayToExecute', '0'),
            new Parameter('affiliateId', ''),
            new Parameter('includeWarningColumn', 'true'),
            new Parameter('sortBy', 'orderDefault'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'programId'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'affiliateId'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'applicationDate'),
            new Parameter('autoCheckbox', 'columns'),
            new Parameter('columns', 'status'),
            new Parameter('autoCheckbox', 'useMetricColumn'),
            new Parameter('customKeyMetricCount', '0'),
            new Parameter('metric1.name', ''),
            new Parameter('metric1.midFactor', ''),
            new Parameter('metric1.midOperator', '/'),
            new Parameter('metric1.columnName1', 'programId'),
            new Parameter('metric1.operator1', '/'),
            new Parameter('metric1.columnName2', 'programId'),
            new Parameter('metric1.lastOperator', '/'),
            new Parameter('metric1.factor', ''),
            new Parameter('metric1.summaryType', 'NONE'),
            new Parameter('format', 'CSV'),
            new Parameter('separator', ','),
            new Parameter('dateType', '0'),
            new Parameter('favoriteId', ''),
            new Parameter('favoriteName', ''),
            new Parameter('favoriteDescription', ''),
            new Parameter('programAffiliateStatusId', '4')
        ];
        $urls = [];
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/aReport3Internal.action?', $valuesFormExport);
        $exportReport = $this->_client->post($urls);
        $exportReport[0] = self::checkReportError($exportReport[0], $urls[0]);
        $merchantReportListAux = self::getExportMerchantReport($exportReport[0]);
        foreach ($merchantReportListAux as $key => $value) {
            $merchantReportList[$key] = $value;
        }
        return $merchantReportList;
    }

    public function checkReportError($content, $request, $try = 0)
    {

        if (preg_match('/\/report\/published\/aAffiliateEventBreakdownReport/', $content, $matches)) {
            //report too big, we have to download it and read it
            if (preg_match('/(\/report\/published\/(aAffiliateEventBreakdownReport(.*))\.zip)/', $content, $matches)) {

                $file = "http://publisher.tradedoubler.com" . $matches[0];
                $newfile = realpath(dirname(COOKIES_BASE_DIR)) . '/pdf/' . $matches[2] . '.zip';

                if (!copy($file, $newfile)) {
                    throw new Exception('Failing copying the zip file \n\n');
                }
                $zip = new ZipArchive();
                if ($zip->open($newfile, ZIPARCHIVE::CREATE) !== TRUE) {
                    throw new Exception('Cannot open zip file \n\n');
                }
                $zip->extractTo(realpath(dirname(COOKIES_BASE_DIR)) . '/pdf/');
                $zip->close();

                $unzipFilePath = realpath(dirname(COOKIES_BASE_DIR)) . '/pdf/' . $matches[2];
                $fileContent = file_get_contents($unzipFilePath);
                unlink($newfile);
                unlink($unzipFilePath);
                return $fileContent;
            }

            throw new Exception('Report too big \n\n');

        } elseif (preg_match("/ error/", $content, $matches)) {
            $urls = [];
            $urls[] = $request;
            $exportReport = $this->_client->get($urls);
            $try++;
            if ($try < 5) {
                return self::checkReportError($exportReport[0], $request, $try);
            } else {
                throw new Exception('Problem checking report\n\n');
            }

        } else {
            return $content;
        }

    }

    /**
     * @param $content
     * @return array
     */
    private function getExportMerchantReport($content)
    {

        $merchantReport = self::formatCsv($content);

        $exportData = str_getcsv($merchantReport, "\r\n");
        $merchantReportList = [];
        $num = count($exportData);
        $websiteMap = [];
        for ($i = 3; $i < $num; $i++) {
            $merchantExportArray = str_getcsv($exportData[$i], ",");

            if ($merchantExportArray[2] != '' && $merchantExportArray[4] != '') {
                $merchantReportList[$merchantExportArray[4]] = $merchantExportArray[2];
                $websiteMap[$merchantExportArray[0]] = "";
            }

        }
        return $merchantReportList;
    }

    /**
     * @param $csv
     * @return mixed
     */
    private function formatCsv($csv)
    {
        preg_match_all("/\"([^\"]+?)\",/", $csv, $matches);
        foreach ($matches[1] as $match) {
            if (preg_match("/,/", $match)) {
                $rep = preg_replace("/,/", "", $match);
                $csv = str_replace($match, $rep, $csv);
                $match = $rep;
            }
            if (preg_match("/\n/", $match)) {
                $rep = preg_replace("/\n/", "", $match);
                $csv = str_replace($match, $rep, $csv);
            }
        }
        return $csv;
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
        $merchantIdList = Utilities::getMerchantIdMapFromMerchantList($merchantList);

        $totalTransactions = [];
        $valuesFormExport = [
            new Parameter('reportName', 'aAffiliateEventBreakdownReport'),
            new Parameter('columns', 'programId'),
            new Parameter('columns', 'timeOfVisit'),
            new Parameter('columns', 'timeOfEvent'),
            new Parameter('columns', 'timeInSession'),
            new Parameter('columns', 'lastModified'),
            new Parameter('columns', 'epi1'),
            new Parameter('columns', 'eventName'),
            new Parameter('columns', 'pendingStatus'),
            new Parameter('columns', 'siteName'),
            new Parameter('columns', 'graphicalElementName'),
            new Parameter('columns', 'graphicalElementId'),
            new Parameter('columns', 'productName'),
            new Parameter('columns', 'productNrOf'),
            new Parameter('columns', 'productValue'),
            new Parameter('columns', 'affiliateCommission'),
            new Parameter('columns', 'link'),
            new Parameter('columns', 'leadNR'),
            new Parameter('columns', 'orderNR'),
            new Parameter('columns', 'pendingReason'),
            new Parameter('columns', 'orderValue'),
            new Parameter('isPostBack', ''),
            new Parameter('metric1.lastOperator', '/'),
            new Parameter('interval', ''),
            new Parameter('favoriteDescription', ''),
            new Parameter('event_id', '0'),
            new Parameter('pending_status', '1'),
            new Parameter('run_as_organization_id', ''),
            new Parameter('minRelativeIntervalStartTime', '0'),
            new Parameter('includeWarningColumn', 'true'),
            new Parameter('metric1.summaryType', 'NONE'),
            new Parameter('metric1.operator1', '/'),
            new Parameter('latestDayToExecute', '0'),
            new Parameter('showAdvanced', 'true'),
            new Parameter('breakdownOption', '1'),
            new Parameter('metric1.midFactor', ''),
            new Parameter('reportTitleTextKey', 'REPORT3_SERVICE_REPORTS_AAFFILIATEEVENTBREAKDOWNREPORT_TITLE'),
            new Parameter('setColumns', 'true'),
            new Parameter('metric1.columnName1', 'orderValue'),
            new Parameter('metric1.columnName2', 'orderValue'),
            new Parameter('reportPrograms', ''),
            new Parameter('metric1.midOperator', '/'),
            new Parameter('dateSelectionType', '1'),
            new Parameter('favoriteName', ''),
            new Parameter('affiliateId', ''),
            new Parameter('dateType', '1'),
            new Parameter('period', 'custom_period'),
            new Parameter('tabMenuName', ''),
            new Parameter('maxIntervalSize', '0'),
            new Parameter('favoriteId', ''),
            new Parameter('sortBy', 'timeOfEvent'),
            new Parameter('metric1.name', ''),
            new Parameter('customKeyMetricCount', '0'),
            new Parameter('metric1.factor', ''),
            new Parameter('showFavorite', 'false'),
            new Parameter('separator', ','),
            new Parameter('format', 'CSV')
        ];
        $valuesFormExport[] = new Parameter('startDate', self::formatDate($dStartDate));
        $valuesFormExport[] = new Parameter('endDate', self::formatDate($dEndDate));
        $urls = [];
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/aReport3Internal.action?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);

        $exportReport[0] = self::checkReportError($exportReport[0], $urls[0]);
        $exportData = str_getcsv($exportReport[0], "\r\n");
        $num = count($exportData);
        for ($i = 2; $i < $num - 1; $i++) {

            $transactionExportArray = str_getcsv($exportData[$i], ",");

            if (!isset($transactionExportArray[2])) {
                throw new Exception('Problem getting transaction\n\n');
            }
            if (count($this->_sitesAllowed) == 0 || in_array($transactionExportArray[13], $this->_sitesAllowed)) {


                if ($transactionExportArray[0] !== '' && isset($merchantIdList[(int)$transactionExportArray[2]])) {

                    $transaction = [];
                    $transaction['merchantId'] = $transactionExportArray[2];
                    $transactionDate = self::toDate(substr($transactionExportArray[4], 0, -6));

                    $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                    if ($transactionExportArray[8] != '') {
                        $transaction['unique_id'] = substr($transactionExportArray[8], 0, 200);
                    } elseif ($transactionExportArray[7] != '') {
                        $transaction['unique_id'] = substr($transactionExportArray[7], 0, 200);
                    } else {
                        throw new Exception("No Identifier");
                    }


                    if ($transactionExportArray[9] != '') {
                        $transaction['custom_id'] = $transactionExportArray[9];
                    }

                    if ($transactionExportArray[11] == 'A') {
                        $transaction['status'] = Utilities::STATUS_CONFIRMED;
                    } elseif ($transactionExportArray[11] == 'P') {
                        $transaction['status'] = Utilities::STATUS_PENDING;
                    } elseif ($transactionExportArray[11] == 'D') {
                        $transaction['status'] = Utilities::STATUS_DECLINED;
                    }

                    if ($transactionExportArray[19] != '') {
                        $transaction['amount'] = Utilities::parseDouble($transactionExportArray[19]);
                    } else {
                        $transaction['amount'] = Utilities::parseDouble($transactionExportArray[20]);
                    }

                    $transaction['commission'] = Utilities::parseDouble($transactionExportArray[20]);
                    $totalTransactions[] = $transaction;
                }
            }
        }
        return $totalTransactions;
    }

    /**
     * @param $date
     * @return string
     * @throws Exception
     */
    protected function formatDate($date)
    {
        if ($this->_dateFormat == 'dd/MM/yy') {
            $dateString = $date->format('d/m/Y');
        } elseif ($this->_dateFormat == 'M/d/yy') {
            $dateString = $date->format('n/j/y');
        } elseif ($this->_dateFormat == 'd/MM/yy') {
            $dateString = $date->format('j/m/y');
        } elseif ($this->_dateFormat == 'tt.MM.uu') {
            $dateString = $date->format('d.m.y');
        } elseif ($this->_dateFormat == 'jj-MM-aa') {
            $dateString = $date->format('d-m-y');
        } elseif ($this->_dateFormat == 'jj/MM/aa') {
            $dateString = $date->format('d/m/y');
        } elseif ($this->_dateFormat == 'dd.MM.yy') {
            $dateString = $date->format('d.m.y');
        } elseif ($this->_dateFormat == 'yy-MM-dd') {
            $dateString = $date->format('y-m-d');
        } elseif ($this->_dateFormat == 'd-M-yy') {
            $dateString = $date->format('j-n-y');
        } elseif ($this->_dateFormat == 'yyyy/MM/dd') {
            $dateString = $date->format('Y/m/d');
        } elseif ($this->_dateFormat == 'yyyy-MM-dd') {
            $dateString = $date->format('Y-m-d');
        } else {
            throw new Exception("\n Date Format not supported " . $this->_dateFormat . "\n");
        }
        return $dateString;
    }

    /**
     * @param $dateString
     * @return DateTime|null
     * @throws Exception
     */
    protected function toDate($dateString)
    {
        $transactionDate = false;
        $hour_separator = ':';
        if (strlen($dateString) > 10) {
            if (strpos(substr($dateString, 10), '.') !== false) {
                $hour_separator = '.';
            }
        }
        if ($this->_dateFormat == 'dd/MM/yy') {
            $transactionDate = DateTime::createFromFormat("d/m/y H{$hour_separator}i{$hour_separator}s", trim($dateString));
        } elseif ($this->_dateFormat == 'M/d/yy') {
            // Check for AM/PM time - 2019-04-15 <PN>
            $transactionDate = DateTime::createFromFormat("m/d/y h{$hour_separator}i{$hour_separator}s A", trim($dateString));
            if ($transactionDate === false) {
                // Check for H24 time
                $transactionDate = DateTime::createFromFormat("m/d/y H{$hour_separator}i{$hour_separator}s", trim($dateString));
            }
            if ($transactionDate === false) {
                // Try to get only the date
                $pos = strpos($dateString, ' ');
                if ($pos !== false) {
                    $dateString = substr($dateString, 0, $pos);
                    $transactionDate = DateTime::createFromFormat("m/d/y", trim($dateString));
                }
            }
        } elseif ($this->_dateFormat == 'd/MM/yy') {
            $transactionDate = DateTime::createFromFormat("j/m/y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'tt.MM.uu') {
            $transactionDate = DateTime::createFromFormat("d.m.y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'jj-MM-aa') {
            $transactionDate = DateTime::createFromFormat("d-m-y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'jj/MM/aa') {
            $transactionDate = DateTime::createFromFormat("d/m/y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'dd.MM.yy') {
            $transactionDate = DateTime::createFromFormat("d.m.y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'yy-MM-dd') {
            $transactionDate = DateTime::createFromFormat("y-m-d H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'd-M-yy') {
            $transactionDate = DateTime::createFromFormat("j-n-y H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'yyyy/MM/dd') {
            $transactionDate = DateTime::createFromFormat("Y/m/d H:i:s", trim($dateString));
        } elseif ($this->_dateFormat == 'yyyy-MM-dd') {
            $transactionDate = DateTime::createFromFormat("Y-m-d H:i:s", trim($dateString));
        } else {
            throw new Exception("\n Date Format not supported " . $this->_dateFormat . "\n");
        }
        if ($transactionDate === false) {
            throw new Exception("TradeDoubler - Date Format not supported " . $this->_dateFormat . " for date: " . $dateString . "\n");
        }
        return $transactionDate;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPaymentHistory()
    {
        $paymentHistory = [];

        $urls = [];
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/reportSelection/Payment?', []);
        $exportReport = $this->_client->get($urls);
        /*** load the html into the object ***/
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->validateOnParse = true;
        $doc->loadHTML($exportReport[0]);
        $selectList = $doc->getElementsByTagName('select');
        $paymentSelect = null;
        if ($selectList->length > 0) {
            // looking for the payments select
            $it = 0;
            while ($it < $selectList->length) {
                $selectName = $selectList->item($it)->attributes->getNamedItem('name')->nodeValue;
                if ($selectName == 'payment_id') {
                    $paymentSelect = $selectList->item($it);
                    break;
                }
                $it++;
            }
            if ($paymentSelect != null) {
                $paymentLines = $paymentSelect->childNodes;
                for ($i = 0; $i < $paymentLines->length; $i++) {
                    $pid = $paymentLines->item($i)->attributes->getNamedItem("value")->nodeValue;
                    if (is_numeric($pid)) {
                        $obj = [];

                        $paymentLine = $paymentLines->item($i)->nodeValue;
                        $value = preg_replace('/[^0-9\.,]/', "", substr($paymentLine, 10));

                        $paymentParts = explode(" ", $paymentLine);
                        $date = self::toDate($paymentParts[0] . " 00:00:00");

                        $obj['date'] = $date->format("Y-m-d H:i:s");
                        $obj['pid'] = $pid;
                        $obj['method'] = 'BACS';
                        $obj['value'] = Utilities::parseDouble($value);

                        $paymentHistory[] = $obj;
                    }
                }
            }
        }
        return $paymentHistory;
    }

    /**
     * @param $paymentId
     * @return array
     * @throws Exception
     */
    public function paymentTransactions($paymentId)
    {
        $transactionList = [];

        $urls = [];
        $valuesFormExport = [];
        $valuesFormExport[] = new Parameter('popup', 'true');
        $valuesFormExport[] = new Parameter('payment_id', $paymentId);
        $urls[] = new Request('http://publisher.tradedoubler.com/pan/reports/Payment.html?', $valuesFormExport);
        $exportReport = $this->_client->get($urls);


        $dom = new Query($exportReport[0]);
        $results = $dom->execute('//a');

        $urls = [];
        foreach ($results as $result) {
            $url = $result->getAttribute('href');
            $urls[] = new Request("http://publisher.tradedoubler.com" . $url . "&format=CSV", []);
        }
        $exportReportList = $this->_client->get($urls);
        foreach ($exportReportList as $exportReport) {
            $exportReportData = str_getcsv($exportReport, "\r\n");
            $num = count($exportReportData);
            for ($i = 2; $i < $num - 1; $i++) {
                $transactionExportArray = str_getcsv($exportReportData[$i], ";");
                if (count($this->_sitesAllowed) == 0 || in_array($transactionExportArray[2], $this->_sitesAllowed)) {
                    $transaction = [];
                    $transaction['merchantId'] = $transactionExportArray[2];
                    $transactionDate = self::toDate($transactionExportArray[6]);
                    $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                    if ($transactionExportArray[8] != '') {
                        $transaction['unique_id'] = substr($transactionExportArray[8], 0, 200);
                    } elseif ($transactionExportArray[7] != '') {
                        $transaction['unique_id'] = substr($transactionExportArray[7], 0, 200);
                    } else {
                        throw new Exception("No Identifier");
                    }


                    if ($transactionExportArray[9] != '') {
                        $transaction['custom_id'] = $transactionExportArray[9];
                    }

                    if ($transactionExportArray[11] == 'A') {
                        $transaction['status'] = Utilities::STATUS_CONFIRMED;
                    } elseif ($transactionExportArray[11] == 'P') {
                        $transaction['status'] = Utilities::STATUS_PENDING;
                    } elseif ($transactionExportArray[11] == 'D') {
                        $transaction['status'] = Utilities::STATUS_DECLINED;
                    }

                    if ($transactionExportArray[13] != '') {
                        $transaction['amount'] = Utilities::parseDouble($transactionExportArray[13]);
                    } else {
                        $transaction['amount'] = Utilities::parseDouble($transactionExportArray[14]);
                    }

                    $transaction['commission'] = Utilities::parseDouble($transactionExportArray[15]);
                    $transactionList[] = $transaction;
                }
            }
        }

        return $transactionList;
    }
}
