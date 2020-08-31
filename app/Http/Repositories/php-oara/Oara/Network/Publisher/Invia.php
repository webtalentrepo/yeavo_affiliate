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
class Invia extends Network
{

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {

        $user = $credentials ['user'];
        $password = $credentials ['password'];
        $this->_client = new Access($credentials);

        $loginUrl = 'http://partner2.invia.cz/';
        $valuesLogin = [
            new Parameter ('ac-email', $user),
            new Parameter ('ac-password', $password),
            new Parameter ('redir_url', 'http://partner2.invia.cz/'),
            new Parameter ('ac-submit', '1'),
            new Parameter ('k2form_login', '1')
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
        $urls[] = new Request("http://partner2.invia.cz/", []);
        $exportReport = $this->_client->get($urls);

        if (preg_match("/odhlaseni/", $exportReport[0], $matches)) {
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
        $obj['name'] = 'Invia';
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
            new Parameter('AffilUI_Filter', ''),
            new Parameter('AffilUI_FilterStr', ''),
            new Parameter('AffilUI_FilterTag', ''),
            new Parameter('AdvancedFilter_State', '0'),
            new Parameter('AdvancedFilter_nl_stav_id', '0'),
            new Parameter('AdvancedFilter_nl_invia_id', '1'),
            new Parameter('AdvancedFilter_departure', '0'),
            new Parameter('AdvancedFilter_b_show_invoiced', 'on'),
            new Parameter('AdvancedFilter_date_from', '01.01.2014'),
            new Parameter('AdvancedFilter_date_to', '31.10.2014'),
            new Parameter('AdvancedFilter_nl_rows', ''),
            new Parameter('AdvancedFilter_sent', '1')
        ];

        $urls = [];
        $urls[] = new Request("http://partner2.invia.cz/ikomunity/index.php?k2MAIN[action]=AFFIL_OBJ", $valuesFromExport);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $tableList = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " k2table_AffilUI ")]');
        if ($tableList->length > 0) {

            $exportData = Utilities::htmlToCsv(Utilities::DOMinnerHTML($tableList->item(0)));

            $num = count($exportData);
            for ($i = 1; $i < $num - 1; $i++) {
                $transactionExportArray = explode(";", $exportData [$i]);
                $transaction = [];
                $transactionDate = DateTime::createFromFormat("d.m.Y", $transactionExportArray [2]);
                $transaction ['date'] = $transactionDate->format("Y-m-d H:i:s");
                $status = $transactionExportArray [4];
                if ($status == "Zaplaceno") {
                    $transaction ['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($status == "Neprod�no") {
                    $transaction ['status'] = Utilities::STATUS_PENDING;
                } elseif ($status == "Storno") {
                    $transaction ['status'] = Utilities::STATUS_DECLINED;
                } else {
                    throw new Exception ("New status found {$status}");
                }
                $transaction ['amount'] = Utilities::parseDouble($transactionExportArray [6]);
                $transaction ['commission'] = Utilities::parseDouble($transactionExportArray [6]);
                $transaction ['merchantId'] = 1;
                $transaction ['unique_id'] = $transactionExportArray [0];
                $totalTransactions [] = $transaction;
            }
        }
        return $totalTransactions;
    }
}
