<?php

namespace Oara\Network\Publisher;
use DateTime;
use Oara\Network;
use Oara\Utilities;
use function count;
use function file_get_contents;
use function simplexml_load_string;
use function str_getcsv;
use function utf8_encode;

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
 * @author     Carlos Morillo Merino
 * @category   Efiliation
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Effiliation extends Network
{

    protected $_credentials = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {

        $this->_credentials = $credentials;

    }

    /**
     * @return array
     */
    public function getNeededCredentials()
    {
        $credentials = [];

        $parameter = [];
        $parameter["description"] = "API Password";
        $parameter["required"] = true;
        $parameter["name"] = "API";
        $credentials["apipassword"] = $parameter;

        return $credentials;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        // BV-712 don't check connection downloading transactions without parameters ...
        // ... because API don't allow the same query to be repeated if parameters don't change.
        return true;

        /*
        $connection = false;

        $url = 'https://api.effiliation.com/apiv2/transaction.csv?key=' . $this->_credentials["apiPassword"] . '&start=' . $dStartDate->format("d/m/Y") . '&end=' . $dEndDate->format("d/m/Y") . '&type=date';

        $content = \file_get_contents('https://api.effiliation.com/apiv2/transaction.csv?key=' . $this->_credentials["apiPassword"]);
        if (!\preg_match("/bad credentials !/", $content, $matches)) {
            $connection = true;
        }
        return $connection;
        */
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = [];
        $url = 'https://api.effiliation.com/apiv2/programs.xml?key=' . $this->_credentials["apiPassword"] . "&filter=active&timestamp=" . time();
        $content = @file_get_contents($url);
        $xml = simplexml_load_string($content, null, LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach ($xml->program as $merchant) {
            $obj = [];
            $obj['cid'] = (string)$merchant->id_programme;
            $obj['name'] = (string)$merchant->nom;
            $obj['url'] = "";
            $merchants[] = $obj;
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

        $merchantIdList = Utilities::getMerchantIdMapFromMerchantList($merchantList);

        $url = 'https://api.effiliation.com/apiv2/transaction.csv?key=' . $this->_credentials["apiPassword"] . '&start=' . $dStartDate->format("d/m/Y") . '&end=' . $dEndDate->format("d/m/Y") . '&type=date&timestamp=' . time();
        $content = utf8_encode(file_get_contents($url));
        $exportData = str_getcsv($content, "\n");
        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {
            $transactionExportArray = str_getcsv($exportData[$i], "|");
            if (isset($merchantIdList[(int)$transactionExportArray[2]])) {

                $transaction = [];
                $merchantId = (int)$transactionExportArray[2];
                $transaction['merchantId'] = $merchantId;
                // Changed Transaction date index from 10 to 12 - 2018-01-01 <PN>
                $transaction['date'] = $transactionExportArray[12];
                $transaction['unique_id'] = $transactionExportArray[0];
                $transaction['status'] = Utilities::STATUS_PENDING;
                if ($transactionExportArray[15] != null) {
                    $transaction['custom_id'] = $transactionExportArray[15];
                }

                if ($transactionExportArray[9] == 'Valide') {
                    $transaction['status'] = Utilities::STATUS_CONFIRMED;
                } elseif ($transactionExportArray[9] == 'Attente') {
                    $transaction['status'] = Utilities::STATUS_PENDING;
                } elseif ($transactionExportArray[9] == 'Refusé') {
                    $transaction['status'] = Utilities::STATUS_DECLINED;
                }
                $transaction['amount'] = Utilities::parseDouble($transactionExportArray[7]);
                $transaction['commission'] = Utilities::parseDouble($transactionExportArray[8]);
                $totalTransactions[] = $transaction;
            }
        }

        return $totalTransactions;
    }

}
