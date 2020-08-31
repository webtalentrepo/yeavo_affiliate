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
use function explode;
use function is_numeric;
use function preg_match;

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
class Afilio extends Network
{

    /**
     * @var null
     */
    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $user = $credentials ['user'];
        $password = $credentials ['password'];

        $this->_client = new Access ($credentials);

        $loginUrl = 'http://v2.afilio.com.br/index.php';

        $valuesLogin = [
            new Parameter ('auth_login', $user),
            new Parameter ('auth_pass', $password),
            new Parameter ('auth_type', "aff"),
            new Parameter ('Ok', "ok"),
            new Parameter ('from', "afilio"),
            new Parameter ('url_error', "http://www.afilio.com.br/login-incorreto"),
            new Parameter ('id_regie', "3")
        ];

        $urls = [];
        $urls [] = new Request ($loginUrl, $valuesLogin);
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
        $urls [] = new Request ('http://v2.afilio.com.br/aff/', []);
        $exportReport = $this->_client->get($urls);
        if (preg_match("/logout/", $exportReport [0], $matches)) {
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
        $urls = [];
        $urls [] = new Request ('http://v2.afilio.com.br/aff/aff_manage_sale.php', $valuesFromExport);
        $exportReport = $this->_client->get($urls);


        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " p_nProgId ")]');
        $merchantLines = $results->item(0)->childNodes;
        for ($i = 0; $i < $merchantLines->length; $i++) {
            $cid = $merchantLines->item($i)->attributes->getNamedItem("value")->nodeValue;
            if (is_numeric($cid)) {
                $name = $merchantLines->item($i)->nodeValue;
                $obj = [];
                $obj ['cid'] = $cid;
                $obj ['name'] = $name;
                $merchants [] = $obj;
            }
        }

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

        $merchantMap = Utilities::getMerchantNameMapFromMerchantList($merchantList);

        $valuesFromExport = [];
        $valuesFromExport [] = new Parameter ('getExcel', '1');
        $valuesFromExport [] = new Parameter ('p_sSearchMode', 'custom');
        $valuesFromExport [] = new Parameter ('p_nType', 'sale');
        $valuesFromExport [] = new Parameter ('p_sPeriod', 'day');
        $valuesFromExport [] = new Parameter ('export', 'csv');
        $valuesFromExport [] = new Parameter ('p_nStatus', '3');
        $valuesFromExport [] = new Parameter ('p_nNbRowsByPage', '50');
        $valuesFromExport [] = new Parameter ('p_nProgId', '');
        $valuesFromExport [] = new Parameter ('p_sStartDate', $dStartDate->format("d/m/Y"));
        $valuesFromExport [] = new Parameter ('p_sEndDate', $dEndDate->format("d/m/Y"));
        $valuesFromExport [] = new Parameter ('p_nPage', '1');

        $urls = [];
        $urls [] = new Request ('http://v2.afilio.com.br/include/lib/aff_lib_manage_sale.php?', $valuesFromExport);

        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//table');
        $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(1)));
        $num = count($exportData);
        for ($i = 0; $i < $num; $i++) {
            $transactionExportArray = explode(";,", $exportData [$i]);

            if (isset ($merchantMap[$transactionExportArray [0]])) {

                $transaction = [];
                $transaction ['merchantId'] = $merchantMap[$transactionExportArray [0]];
                $transaction ['unique_id'] = $transactionExportArray [4];
                $transactionDate = DateTime::createFromFormat('d/m/yy H:i:s', $transactionExportArray [1]);
                $transaction ['date'] = $transactionDate->format("Y-m-d H:i:s");
                $transaction ['customId'] = $transactionExportArray [5];

                if ($transactionExportArray [7] == "Accepted" || $transactionExportArray [7] == "Accepté" || $transactionExportArray [7] == "Aceito") {
                    $transaction ['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($transactionExportArray [7] == "Pending" || $transactionExportArray [7] == "En attente" || $transactionExportArray [7] == "Pendente") {
                    $transaction ['status'] = Utilities::STATUS_PENDING;
                } elseif ($transactionExportArray [7] == "Rejected" || $transactionExportArray [7] == "Refusé" || $transactionExportArray [7] == "Refused" || $transactionExportArray [7] == "Recusado") {
                    $transaction ['status'] = Utilities::STATUS_DECLINED;
                } else {
                    throw new Exception ("New status found {$transactionExportArray [7]}");
                }

                $transaction ['amount'] = Utilities::parseDouble($transactionExportArray [6]);
                $transaction ['commission'] = Utilities::parseDouble($transactionExportArray [6]);

                $totalTransactions [] = $transaction;
            }
        }

        return $totalTransactions;
    }

}
