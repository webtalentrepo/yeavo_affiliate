<?php

namespace Oara\Network\Publisher;
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
 * @author     Alejandro Muñoz Odero
 * @category   PureVPN
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PureVPN extends Network
{

    private $_credentials = null;
    private $_s = null;
    private $_options = [];
    private $_transactionList = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $valuesLogin = [
            new Parameter('username', $this->_credentials['user']),
            new Parameter('password', $this->_credentials['password']),
        ];
        $this->_client = new Access($credentials);

        $urls = [];
        $urls[] = new Request("https://billing.purevpn.com/clientarea.php", []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//input[@name="token"]');
        foreach ($hidden as $values) {
            $valuesLogin[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }

        $urls = [];
        $urls[] = new Request("https://billing.purevpn.com/dologin.php?goto=clientarea.php", $valuesLogin);
        $this->_client->post($urls);


        $urls = [];
        $urls[] = new Request("https://billing.purevpn.com/check_affiliate.php?check=affiliate", $valuesLogin);
        $oldOptions = $this->_client->getOptions();
        $newOptions = $oldOptions;
        $newOptions[CURLOPT_HEADER] = true;
        $this->_client->setOptions($newOptions);
        $exportReport = $this->_client->get($urls);
        $this->_client->setOptions($oldOptions);

        preg_match('/Location:(.*?)\n/', $exportReport[0], $matches);
        $newurl = trim(array_pop($matches));
        if (preg_match("/S=(.*)/", $newurl, $matches)) {
            $this->_s = $matches [1];
        }

        $urls = [];
        $urls[] = new Request($newurl, []);
        $this->_client->get($urls);
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
        if ($this->_s == null) {
            $connection = false;
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
        $obj['name'] = "PureVPN";
        $obj['url'] = "https://billing.purevpn.com";
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
        $chip = $this->_s;
        if ($this->_transactionList == null) {

            $urls = [];
            $urls[] = new Request("https://billing.purevpn.com/affiliates/scripts/server.php?C=Pap_Affiliates_Reports_TransactionsGrid&M=getCSVFile&S=$chip&FormRequest=Y&FormResponse=Y", []);
            $exportReport = $this->_client->post($urls);
            $this->_transactionList = str_getcsv($exportReport[0], "\n");
        }
        $exportData = $this->_transactionList;

        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], ",");
            $transaction = [];
            $transaction['merchantId'] = 1;
            $transaction['uniqueId'] = $transactionExportArray[36];
            $transaction['date'] = $transactionExportArray[5];
            $transaction['status'] = Utilities::STATUS_CONFIRMED;
            $transaction['amount'] = Utilities::parseDouble($transactionExportArray[1]);
            $transaction['commission'] = Utilities::parseDouble($transactionExportArray[0]);
            if ($transaction['date'] >= $dStartDate->format("Y-m-d H:i:s") && $transaction['date'] <= $dEndDate->format("Y-m-d H:i:s")) {
                $totalTransactions[] = $transaction;
            }
        }

        return $totalTransactions;
    }
}
