<?php

namespace Oara\Network\Publisher;
use DateTime;
use Oara\Curl\Access;
use Oara\Curl\Parameter;
use Oara\Curl\Request;
use Oara\Network;
use Oara\Utilities;
use function count;
use function preg_match;
use function preg_replace;
use function str_getcsv;
use function var_export;

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
 * @category   SkyScanner
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class SkyScanner extends Network
{

    private $_credentials = null;
    private $_client = null;
    private $_apiKey = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $this->_client = new Access($credentials);

        $valuesLogin = [
            new Parameter('RememberMe', "false"),
            new Parameter('ApiKey', $this->_credentials['user']),
            new Parameter('PortalKey', $this->_credentials['password']),
        ];

        $loginUrl = 'http://business.skyscanner.net/portal/en-GB/SignIn';
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
        $urls = [];
        $urls[] = new Request('http://business.skyscanner.net/portal/en-GB/UK/Report/Show', []);
        $exportReport = $this->_client->get($urls);
        if (!preg_match("/encrypedApiKey: \"(.*)?\",/", $exportReport[0], $match)) {
            $connection = false;
        } else {
            $this->_apiKey = $match[1];
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
        $obj['cid'] = "1";
        $obj['name'] = "SkyScanner";
        $obj['url'] = "http://www.skyscanneraffiliate.net";
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

        $url = 'http://business.skyscanner.net/apiservices/reporting/v1.0/reportdata/' . $dStartDate->format("Y-m-d") . '/' . $dEndDate->format("Y-m-d") . '?encryptedApiKey=' . $this->_apiKey . "&type=csv";
        $urls[] = new Request($url, []);


        $exportReport = $this->_client->get($urls);
        $dump = var_export($exportReport[0], true);
        $dump = preg_replace('/ \. /', "", $dump);
        $dump = preg_replace("/\"\\\\0\"/", "", $dump);
        $dump = preg_replace("/'/", "", $dump);

        $exportData = str_getcsv($dump, "\n");

        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {

            $transactionExportArray = str_getcsv($exportData[$i], ",");
            $transaction = [];
            $transaction['merchantId'] = 1;
            $transactionDate = DateTime::createFromFormat("d/m/Y H:i:s", $transactionExportArray[0]);
            $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
            //unset($transactionDate);
            $transaction['status'] = Utilities::STATUS_CONFIRMED;
            $transaction['amount'] = (double)$transactionExportArray[7];
            $transaction['commission'] = (double)$transaction['amount'] * 0.6;

            if ($transaction['amount'] != 0) {
                $totalTransactions[] = $transaction;
            }

        }

        return $totalTransactions;
    }

}
