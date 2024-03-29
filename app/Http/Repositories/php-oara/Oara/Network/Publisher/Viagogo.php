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
 * @author Carlos Morillo Merino
 * @category Afiliant
 * @copyright Fubra Limited
 * @version Release: 01.00
 *
 */
class Viagogo extends Network
{

    private $_client = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {
        $user = $credentials ['user'];
        $password = $credentials ['password'];
        $this->_client = new Access($credentials);


        $loginUrl = 'https://www.viagogo.co.uk/secure/loginregister/login';

        $valuesLogin = [
            new Parameter ('Login.UserName', $user),
            new Parameter ('Login.Password', $password),
            new Parameter ('ReturnUrl', $loginUrl)
        ];

        $urls = [];
        $urls[] = new Request($loginUrl, $valuesLogin);
        $exportReport = $this->_client->post($urls);

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
        $connection = false;

        $urls = [];
        $urls[] = new Request('https://www.viagogo.co.uk/secure/AffiliateReports', "");
        $exportReport = $this->_client->get($urls);

        if (preg_match("/secure\/loginregister\/logout/", $exportReport[0], $matches)) {
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
        $obj['name'] = 'Viagogo';
        $merchants[] = $obj;

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

        $valuesFromExport = [
            new Parameter('ReportId', '2'),
            new Parameter('Parameters[0].Name', 'ReportPeriod'),
            new Parameter('Parameters[0].Values', 'True'),
            new Parameter('Parameters[1].Name', 'DateFrom'),
            new Parameter('Parameters[1].Values', $dStartDate->format("d/m/Y")),
            new Parameter('Parameters[2].Name', 'DateTo'),
            new Parameter('Parameters[2].Values', $dEndDate->format("d/m/Y")),
            new Parameter('Parameters[3].Name', 'CurrencyCode'),
            new Parameter('Parameters[3].Values', 'GBP'),
            new Parameter('RenderType', 'CSV')
        ];

        $urls = [];
        $urls[] = new Request("https://www.viagogo.co.uk/secure/AffiliateReports/RenderReport", $valuesFromExport);
        $exportReport = $this->_client->post($urls);

        $exportData = str_getcsv($exportReport[0], "\n");
        $num = count($exportData);
        for ($i = 4; $i < $num - 1; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            if (!isset($transactionExportArray[0])) {
                throw new Exception('Problem getting transaction\n\n');
            }
            $transaction = [];
            $transaction['unique_id'] = $transactionExportArray[0];
            if ($transaction['unique_id'] != null) {
                $transaction['merchantId'] = "1";
                $transactionDate = DateTime::createFromFormat("d/m/Y H:i:s", $transactionExportArray[1] . " 00:00:00");
                $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[4]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[6]);
                $totalTransactions[] = $transaction;
            }
        }
        return $totalTransactions;
    }
}
