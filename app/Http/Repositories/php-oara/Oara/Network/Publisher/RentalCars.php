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
use function json_decode;
use function json_encode;
use function simplexml_load_string;

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
 * @category   RentalCars
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class RentalCars extends Network
{
    private $_credentials = null;
    private $_client = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $this->_client = new Access ($this->_credentials);

        $valuesLogin = [
            new Parameter ('login_username', $this->_credentials ['user']),
            new Parameter ('login_password', $this->_credentials ['password'])
        ];

        $loginUrl = 'https://secure.rentalcars.com/affiliates/access?commit=true';
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
     * Check the connection
     */
    public function checkConnection()
    {
        // If not login properly the construct launch an exception
        $connection = false;
        $urls = [];
        $urls [] = new Request ('https://secure.rentalcars.com/affiliates/?master=1', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " header_logout ")]');
        if ($results->length > 0) {
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
        $obj ['cid'] = "1";
        $obj ['name'] = "RentalCars";
        $obj ['url'] = "https://secure.rentalcars.com";
        $merchants [] = $obj;

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


        $cancelledMap = [];
        $valuesFormExport = [];
        $valuesFormExport [] = new Parameter ('cancelled', 'cancelled');

        $urls = [];
        $urls [] = new Request ('https://secure.rentalcars.com/affiliates/booked_excel?date_start=' . $dStartDate->format("Y-m-d") . '&date_end=' . $dEndDate->format("Y-m-d"), $valuesFormExport);
        $exportReport = $this->_client->post($urls);

        $xml = simplexml_load_string($exportReport [0]);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $headerIndex = [];
        for ($i = 0; $i < count($array["Worksheet"]["Table"]["Row"][2]["Cell"]); $i++) {
            $headerIndex[$i] = $array["Worksheet"]["Table"]["Row"][2]["Cell"][$i]["Data"];
        }
        for ($z = 3; $z < count($array["Worksheet"]["Table"]["Row"]) - 2; $z++) {
            $transactionDetails = [];
            for ($i = 0; $i < count($array["Worksheet"]["Table"]["Row"][$z]["Cell"]); $i++) {
                if (isset($array["Worksheet"]["Table"]["Row"][$z]["Cell"][$i]["Data"])) {
                    $transactionDetails[$headerIndex[$i]] = $array["Worksheet"]["Table"]["Row"][$z]["Cell"][$i]["Data"];
                }
            }
            $cancelledMap[$transactionDetails["Res. Number"]] = true;
        }
        $valuesFormExport = [];
        $valuesFormExport [] = new Parameter ('booking', 'booking');

        $urls = [];
        $urls [] = new Request ('https://secure.rentalcars.com/affiliates/booked_excel?date_start=' . $dStartDate->format("Y-m-d") . '&date_end=' . $dEndDate->format("Y-m-d"), $valuesFormExport);
        $exportReport = $this->_client->post($urls);

        $xml = simplexml_load_string($exportReport [0]);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $headerIndex = [];
        for ($i = 0; $i < count($array["Worksheet"]["Table"]["Row"][2]["Cell"]); $i++) {
            if (isset($array["Worksheet"]["Table"]["Row"][2]["Cell"][$i]["Data"])) {
                $headerIndex[$i] = $array["Worksheet"]["Table"]["Row"][2]["Cell"][$i]["Data"];
            }
        }


        for ($z = 3; $z < count($array["Worksheet"]["Table"]["Row"]) - 2; $z++) {
            $transactionDetails = [];
            for ($i = 0; $i < count($array["Worksheet"]["Table"]["Row"][$z]["Cell"]); $i++) {
                if (isset($array["Worksheet"]["Table"]["Row"][$z]["Cell"][$i]["Data"])) {
                    $transactionDetails[$headerIndex[$i]] = $array["Worksheet"]["Table"]["Row"][$z]["Cell"][$i]["Data"];
                }
            }
            $transaction = [];
            $transaction ['merchantId'] = "1";
            $transaction ['unique_id'] = $transactionDetails["Res. Number"];
            if (isset($transactionDetails["Payment Date"]) && $transactionDetails["Payment Date"] != null) {
                $date = DateTime::createFromFormat("d M Y - H:i", $transactionDetails["Payment Date"]);
            } else {
                $date = DateTime::createFromFormat("d M Y - H:i", $transactionDetails["Book Date"]);
            }


            if (!empty($transactionDetails["AD Campaign"])) {
                $transaction ['custom_id'] = $transactionDetails["AD Campaign"];
            }


            $transaction ['date'] = $date->format("Y-m-d H:i:00");
            if (isset($transactionDetails["Payment Date"]) && $transactionDetails["Payment Date"] != null) {
                $transaction ['status'] = Utilities::STATUS_CONFIRMED;
            } else {
                $transaction ['status'] = Utilities::STATUS_PENDING;
            }


            if (isset($cancelledMap[$transaction ['unique_id']])) {
                $transaction ['status'] = Utilities::STATUS_DECLINED;
            }
            $rate = 0;
            if (isset($transactionDetails["Total Commission"]) && !is_array($transactionDetails["Total Commission"]) && $transactionDetails["Total Commission"] != 0) {
                $rate = $transactionDetails["Booking Value"] / $transactionDetails["Total Commission"];
            }
            $euros = 0;
            if (isset($transactionDetails["Total Commission in Euros"]) && !is_array($transactionDetails["Total Commission in Euros"]) && $transactionDetails["Total Commission in Euros"] != 0) {
                $euros = $transactionDetails["Total Commission in Euros"];
            }
            $transaction ['amount'] = $euros * $rate;
            $transaction ['currency'] = "EUR";
            $transaction ['commission'] = $euros;
            $totalTransactions [] = $transaction;

        }
        return $totalTransactions;
    }
}
