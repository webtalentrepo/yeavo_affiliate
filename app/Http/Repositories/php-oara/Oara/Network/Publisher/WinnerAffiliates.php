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
use function str_getcsv;
use function str_replace;

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
 * @category   WinnerAffiliates
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class WinnerAffiliates extends Network
{

    private $_credentials = null;
    private $_client = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {
        $this->_credentials = $credentials;
        $this->_client = new Access($credentials);

        $valuesLogin = [
            new Parameter('fromUrl', 'https://www.winneraffiliates.com/'),
            new Parameter('username', $this->_credentials['user']),
            new Parameter('password', $this->_credentials['password']),
        ];

        $loginUrl = 'https://www.winneraffiliates.com/login/submit';
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
        $urls[] = new Request('https://www.winneraffiliates.com/', []);
        $exportReport = $this->_client->get($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query('//[@id="lgUsername"]');
        if ($results->length > 0) {
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
        $obj['name'] = "Winner Affiliates";
        $obj['url'] = "https://www.winneraffiliates.com/";
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
        $valuesFromExport[] = new Parameter('periods', 'custom');
        $valuesFromExport[] = new Parameter('minDate', '{"year":"2009","month":"05","day":"01"}');
        $valuesFromExport[] = new Parameter('show_periods', '1');
        $valuesFromExport[] = new Parameter('fromPeriod', $dStartDate->format('Y-m-d'));
        $valuesFromExport[] = new Parameter('toPeriod', $dEndDate->format('Y-m-d'));
        $valuesFromExport[] = new Parameter('product', '');
        $valuesFromExport[] = new Parameter('profile', '');
        $valuesFromExport[] = new Parameter('campaign', '16800');
        $valuesFromExport[] = new Parameter('jsonCampaigns', '{"16800":{"group":{"banner":"Banner","product":"Brand","campaign":"Campaign","platform":"Platform","productType":"Product type","profile":"Profile","date":"Stats date","month":"Stats month","var1":"var1","var2":"var2","var3":"var3","var4":"var4"},"order":{"pokerTournamentFees":"Poker tournament fees","pokerRakes":"Poker rakes","chargebacks":"Chargebacks amt","comps":"Comps amt","credits":"Credit amt","depositsAmount":"Deposits amt","depositsCount":"Deposits cnt","realClicks":"Real clicks","realDownloads":"Real downs","realImpressions":"Real imps","withdrawsAmount":"Withdraws","casinoNetGaming":"Casino Net Gaming","pokerNetGaming":"Poker Net Gaming","pokerSideGamesNG":"Poker Side Games Net Gaming","bingoNetGaming":"Bingo Net Gaming","bingoSideGamesNG":"Bingo Side Games Net Gaming","bingoTotalFDCount":"Bingo Total First Deposit Count","casinoTotalFDCount":"Casino Total First Deposit Count","pokerTotalFDCount":"Poker Total First Deposit Count","casinoTotalRealPlayers":"Casino Total Real Players","bingoTotalRealPlayers":"Bingo Total Real Players","pokerTotalRealPlayers":"Poker Total Real Players","tlrAmount":"Top Level Revenue"}}}');
        $valuesFromExport[] = new Parameter('ts_type', 'advertiser');
        $valuesFromExport[] = new Parameter('reportFirst', 'date');
        $valuesFromExport[] = new Parameter('reportSecond', '');
        $valuesFromExport[] = new Parameter('reportThird', '');
        $valuesFromExport[] = new Parameter('columns[]', 'casinoNetGaming');
        $valuesFromExport[] = new Parameter('columns[]', 'tlrAmount');
        $valuesFromExport[] = new Parameter('csvRequested', 'EXPORT CSV');

        $urls = [];
        $urls[] = new Request('https://www.winneraffiliates.com/traffic-stats/advertiser', $valuesFromExport);
        $exportReport = $this->_client->post($urls);
        $exportData = str_getcsv($exportReport[0], "\n");

        $num = count($exportData);
        for ($i = 1; $i < $num; $i++) {

            $transactionExportArray = str_getcsv($exportData[$i], ",");
            $transaction = [];
            $transaction['merchantId'] = 1;
            $transaction['date'] = $transactionExportArray[0];
            $transaction['status'] = Utilities::STATUS_CONFIRMED;
            $amount = str_replace('$', '', $transactionExportArray[1]);
            $transaction['amount'] = (double)$amount;
            $commission = str_replace('$', '', $transactionExportArray[2]);
            $transaction['commission'] = (double)$commission;

            if ($transaction['amount'] != 0 && $transaction['commission'] != 0) {
                $totalTransactions[] = $transaction;
            }

        }

        return $totalTransactions;
    }

}
