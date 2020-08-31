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
 * @author     Carlos Morillo Merino
 * @category   Bet365
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Bet365 extends Network
{

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

        $urls = [];
        $urls[] = new Request('http://www.bet365affiliates.com/ui/pages/affiliates/affiliates.aspx', []);
        $exportReport = $this->_client->get($urls);


        $valuesLogin = [
            new Parameter('txtUserName', $user),
            new Parameter('txtPassword', $password),
            new Parameter('ctl00%24MasterHeaderPlaceHolder%24ctl00%24userNameTextbox', $user),
            new Parameter('ctl00%24MasterHeaderPlaceHolder%24ctl00%24passwordTextbox', $password),
            new Parameter('ctl00%24MasterHeaderPlaceHolder%24ctl00%24tempPasswordTextbox', 'Password'),
            new Parameter('ctl00%24MasterHeaderPlaceHolder%24ctl00%24goButton.x', '19'),
            new Parameter('ctl00%24MasterHeaderPlaceHolder%24ctl00%24goButton.y', '15')
        ];
        $forbiddenList = ['txtPassword', 'txtUserName'];

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hiddenList = $xpath->query('//input[@type="hidden"]');
        foreach ($hiddenList as $hidden) {
            if (!in_array($hidden->getAttribute("name"), $forbiddenList)) {
                $valuesLogin[] = new Parameter($hidden->getAttribute("name"), $hidden->getAttribute("value"));
            }
        }

        $loginUrl = 'https://www.bet365affiliates.com/Members/CMSitePages/SiteLogin.aspx?lng=1';
        $urls = [];
        $urls[] = new Request('http://www.bet365affiliates.com/ui/pages/affiliates/affiliates.aspx', []);
        $this->_client->post($loginUrl, $valuesLogin);
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
        //If not login properly the construct launch an exception
        $connection = false;
        $urls = [];
        $urls[] = new Request('http://www.bet365affiliates.com/UI/Pages/Affiliates/?', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " ctl00_MasterHeaderPlaceHolder_ctl00_LogoutLinkButton ")]');
        if (count($results) > 0) {
            $connection = true;
        }
        return $connection;
    }

    /**
     * (non-PHPdoc)
     * @see library/Oara/Network/Interface#getMerchantList()
     */
    public function getMerchantList()
    {
        $merchants = [];

        $obj = [];
        $obj['cid'] = 1;
        $obj['name'] = "Bet 365";
        $merchants[] = $obj;

        return $merchants;
    }

    /**
     * (non-PHPdoc)
     * @see library/Oara/Network/Interface#getTransactionList($aMerchantIds, $dStartDate, $dEndDate, $sTransactionStatus)
     */
    public function getTransactionList($merchantList = null, DateTime $dStartDate = null, DateTime $dEndDate = null)
    {

        $totalTransactions = [];

        $valuesFromExport = [];
        $valuesFromExport[] = new Parameter('FromDate', $dStartDate->format("d/m/Y"));
        $valuesFromExport[] = new Parameter('ToDate', $dEndDate->format("d/m/Y"));
        $valuesFromExport[] = new Parameter('ReportType', 'dailyReport');
        $valuesFromExport[] = new Parameter('Link', '-1');

        $urls = [];
        $urls[] = new Request('https://www.bet365affiliates.com/Members/Members/Statistics/Print.aspx?', $valuesFromExport);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " Results ")]');

        if (!preg_match("/No results exist/", $exportReport[0])) {
            $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));
            $num = count($exportData);
            for ($i = 2; $i < $num - 1; $i++) {
                $transactionExportArray = str_getcsv($exportData[$i], ";");

                $transaction = [];
                $transaction['merchantId'] = 1;
                $transactionDate = DateTime::createFromFormat("d-m-Y", $transactionExportArray[1]);
                $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[27]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[32]);
                if ($transaction['amount'] != 0 && $transaction['commission'] != 0) {
                    $totalTransactions[] = $transaction;
                }
            }
        }

        return $totalTransactions;
    }

}
