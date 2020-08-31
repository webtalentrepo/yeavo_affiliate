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
 * @category   ClixGalore
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class ClixGalore extends Network
{
    private $_client = null;
    private $_websiteList = [];

    /**
     * @param $credentials
     * @throws Exception
     * @throws Exception
     * @throws \Oara\Curl\Exception
     */
    public function login($credentials)
    {
        $user = $credentials['user'];
        $password = $credentials['password'];
        $this->_client = new Access($credentials);


        $loginUrl = 'https://www.clixgalore.co.uk/MemberLogin.aspx';
        $valuesLogin = [
            new Parameter('txt_UserName', $user),
            new Parameter('txt_Password', $password),
            new Parameter('cmd_login.x', '29'),
            new Parameter('cmd_login.y', '8')
        ];

        $urls = [];
        $urls[] = new Request($loginUrl, []);
        $exportReport = $this->_client->get($urls);
        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//input[@type="hidden"]');
        foreach ($hidden as $values) {
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
        //If not login properly the construct launch an exception
        $connection = true;
        return $connection;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getMerchantList()
    {
        $merchants = [];

        $urls = [];
        $urls[] = new Request('http://www.clixgalore.co.uk/AffiliateAdvancedReporting.aspx', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " dd_AffAdv_program_list_aff_adv_program_list ")]');

        $count = $results->length;
        if ($count == 1) {
            $selectNode = $results->item(0);
            $merchantLines = $selectNode->childNodes;
            for ($i = 0; $i < $merchantLines->length; $i++) {
                $cid = $merchantLines->item($i)->attributes->getNamedItem("value")->nodeValue;
                if ($cid != 0) {
                    $obj = [];
                    $obj['cid'] = $merchantLines->item($i)->attributes->getNamedItem("value")->nodeValue;
                    $obj['name'] = $merchantLines->item($i)->nodeValue;
                    $obj['url'] = '';
                    $merchants[] = $obj;
                }
            }
        } else {
            throw new Exception('Problem getting the websites');
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
        $statusArray = [0, 1, 2];
        foreach ($statusArray as $status) {
            $valuesFromExport = [
                new Parameter('AfID', '0'),
                new Parameter('S', ''),
                new Parameter('ST', '2'),
                new Parameter('Period', '6'),
                new Parameter('AdID', '0'),
                new Parameter('B', '2')
            ];
            $valuesFromExport[] = new Parameter('SD', $dStartDate->format("Y-m-d"));
            $valuesFromExport[] = new Parameter('ED', $dEndDate->format("Y-m-d"));
            $valuesFromExport[] = new Parameter('Status', $status);

            $urls = [];
            $urls[] = new Request('http://www.clixgalore.co.uk/AffiliateTransactionSentReport_Excel.aspx?', $valuesFromExport);
            $exportReport = $this->_client->get($urls);
            $exportData = Utilities::htmlToCsv($exportReport[0]);
            $num = count($exportData);
            for ($i = 1; $i < $num; $i++) {
                $transactionExportArray = str_getcsv($exportData[$i], ";");
                if (isset($merchantMap[$transactionExportArray[2]])) {
                    $transaction = [];
                    $merchantId = (int)$merchantMap[$transactionExportArray[2]];
                    $transaction['merchantId'] = $merchantId;
                    $transactionDate = DateTime::createFromFormat("d M Y H:m", $transactionExportArray[0]);
                    $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");

                    if ($transactionExportArray[6] != null) {
                        $transaction['custom_id'] = $transactionExportArray[6];
                    }
                    if ($status == 1) {
                        $transaction['status'] = Utilities::STATUS_CONFIRMED;
                    } elseif ($status == 2) {
                        $transaction['status'] = Utilities::STATUS_PENDING;
                    } elseif ($status == 0) {
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
