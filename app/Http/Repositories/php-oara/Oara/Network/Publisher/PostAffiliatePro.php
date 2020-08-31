<?php

namespace Oara\Network\Publisher;
use DateTime;
use Gpf_Api_Session;
use Oara\Network;
use Oara\Utilities;
use Pap_Api_TransactionsGrid;
use function ceil;
use function dirname;
use function realpath;

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
 * @category   PureVPN
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class PostAffiliatePro extends Network
{
    public $_credentials = null;
    public $_session = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        include realpath(dirname(__FILE__)) . "/PostAffiliatePro/PapApi.class.php";
        $this->_credentials = $credentials;
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
        // If not login properly the construct launch an exception
        $connection = true;
        $session = new Gpf_Api_Session("http://" . $this->_credentials["domain"] . "/scripts/server.php");
        if (!@$session->login($this->_credentials ["user"], $this->_credentials ["password"], Gpf_Api_Session::AFFILIATE)) {
            $connection = false;
        }
        $this->_session = $session;

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
        $obj ['name'] = "Post Affiliate Pro ({$this->_credentials["domain"]})";
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


        //----------------------------------------------
        // get recordset of list of transactions
        $request = new Pap_Api_TransactionsGrid($this->_session);
        // set filter
        $request->addFilter('dateinserted', 'D>=', $dStartDate->format("Y-m-d"));
        $request->addFilter('dateinserted', 'D<=', $dEndDate->format("Y-m-d"));
        $request->setLimit(0, 100);
        $request->setSorting('t_orderid', false);
        $request->sendNow();
        $grid = $request->getGrid();
        $recordset = $grid->getRecordset();
        // iterate through the records
        foreach ($recordset as $rec) {
            $transaction = [];
            $transaction ['merchantId'] = 1;
            $transaction ['uniqueId'] = $rec->get('t_orderid');
            $transaction ['date'] = $rec->get('dateinserted');
            $transaction ['status'] = Utilities::STATUS_CONFIRMED;
            $transaction ['amount'] = Utilities::parseDouble($rec->get('totalcost'));
            $transaction ['commission'] = Utilities::parseDouble($rec->get('commission'));
            $totalTransactions [] = $transaction;
        }
        //----------------------------------------------
        // in case there are more than 30 records total
        // we should load and display the rest of the records
        // in the cycle
        $totalRecords = $grid->getTotalCount();
        $maxRecords = $recordset->getSize();
        if ($maxRecords > 0) {
            $cycles = ceil($totalRecords / $maxRecords);
            for ($i = 1; $i < $cycles; $i++) {
                // now get next 30 records
                $request->setLimit($i * $maxRecords, $maxRecords);
                $request->sendNow();
                $recordset = $request->getGrid()->getRecordset();
                // iterate through the records
                foreach ($recordset as $rec) {
                    $transaction = [];
                    $transaction ['merchantId'] = 1;
                    $transaction ['uniqueId'] = $rec->get('t_orderid');
                    $transaction ['date'] = $rec->get('dateinserted');
                    if ($rec->get('rstatus') == 'D') {
                        $transaction ['status'] = Utilities::STATUS_DECLINED;
                    } elseif ($rec->get('rstatus') == 'P') {
                        $transaction ['status'] = Utilities::STATUS_PENDING;
                    } elseif ($rec->get('rstatus') == 'A') {
                        $transaction ['status'] = Utilities::STATUS_CONFIRMED;
                    }
                    $transaction ['amount'] = Utilities::parseDouble($rec->get('totalcost'));
                    $transaction ['commission'] = Utilities::parseDouble($rec->get('commission'));
                    $totalTransactions [] = $transaction;
                }
            }
        }
        return $totalTransactions;
    }
}
