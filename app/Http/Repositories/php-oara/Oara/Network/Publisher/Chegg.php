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
use function file_get_contents;
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
 * @category   Chegg
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Chegg extends Network
{

    /**
     * @var null
     */
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
            new Parameter('__EVENTTARGET', ""),
            new Parameter('__EVENTARGUMENT', ""),
            new Parameter('ctl00%24ContentPlaceHolder1%24lcLogin%24txtUserName', $user),
            new Parameter('ctl00%24ContentPlaceHolder1%24lcLogin%24txtPassword', $password),
            new Parameter('ctl00%24ContentPlaceHolder1%24lcLogin%24btnSubmit', 'Login'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtFirstName', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtLastName', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtEmail', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtNewPassword', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtIM', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddIMNetwork', '0'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtPhone', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtFax', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtBusinessName', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtWebsiteURL', 'http://'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddlBusinessType', '0'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtBusinessDescription', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtAddress1', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtAddress2', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtCity', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddlState', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtOtherState', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtPostalCode', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddlCountry', 'US'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtTaxID', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddPaymentTo', 'Company'),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtSwift', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtAccountName', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtAccountNumber', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtBankRouting', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtBankName', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtBankAddress', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtPayPal', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24txtPayQuickerEmail', ''),
            new Parameter('ctl00%24ContentPlaceHolder1%24scSignup%24ddlReferral', 'Select'),

        ];
        $html = file_get_contents("http://cheggaffiliateprogram.com/Welcome/LogInAndSignUp.aspx?FP=C&FR=1&S=4");

        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//input[@type="hidden"]');
        foreach ($hidden as $values) {
            $valuesLogin[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }

        $loginUrl = 'http://cheggaffiliateprogram.com/Welcome/LogInAndSignUp.aspx?FP=C&FR=1&S=2';
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
        $urls[] = new Request('http://cheggaffiliateprogram.com/Home.aspx?', []);
        $exportReport = $this->_client->get($urls);

        if (preg_match('/Welcome\/Logout\.aspx/', $exportReport[0])) {
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
        $obj['name'] = "Bet 365";
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


            $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->current()));
            $num = count($exportData);
            for ($i = 2; $i < $num - 1; $i++) {
                $transactionExportArray = str_getcsv($exportData[$i], ";");
                $transaction = [];
                $transaction['merchantId'] = 1;
                $transactionDate = DateTime::createFromFormat("d-m-Y", $transactionExportArray[1]);
                $transaction['date'] = $transactionDate->format("Y-m-d 00:00:00");
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
