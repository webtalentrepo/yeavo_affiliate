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
use function file_get_contents;
use function http_build_query;
use function json_decode;
use function json_encode;
use function parse_str;
use function parse_url;
use function stream_context_create;
use function trim;
use function unserialize;

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
 * @category   ShareASale
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Tyroo extends Network
{
    private $_username = null;
    private $_password = null;
    private $_sessionID = null;
    private $_publisherID = null;
    private $_windowid = null;
    private $_sessionIDCurl = null;

    /**
     * @param $credentials
     * @throws Exception
     */
    public function login($credentials)
    {

        $this->_username = $credentials['user'];
        $this->_password = $credentials['password'];
        $this->_client = new Access($credentials);

        $postdata = http_build_query(
            [
                'class'  => 'Logon',
                'method' => 'logon',
                'val1'   => $this->_username,
                'val2'   => $this->_password,
                'val3'   => ''
            ]);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];
        $context = stream_context_create($opts);
        $result = unserialize(file_get_contents('http://www.tyroocentral.com/www/api/v2/xmlrpc/APICall.php', false, $context));
        json_encode($result);
        $this->_sessionID = $result[0];

        $user = $credentials['user'];
        $password = $credentials['password'];

        //webpage uses javascript hex_md5 to encode the password
        $valuesLogin = [
            new Parameter('username', $user),
            new Parameter('password', $password),
            new Parameter('loginByInterface', 1),
            new Parameter('login', 'Login')
        ];

        $urls = [];
        $urls[] = new Request("http://www.tyroocentral.com/www/admin/index.php", $valuesLogin);
        $exportReport = $this->_client->post($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//input[@type="hidden"]');
        foreach ($hidden as $values) {
            $valuesLogin[] = new Parameter($values->getAttribute("name"), $values->getAttribute("value"));
        }

        $urls = [];
        $urls[] = new Request("http://www.tyroocentral.com/www/admin/index.php", $valuesLogin);
        $exportReport = $this->_client->post($urls);

        $doc = new DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new DOMXPath($doc);
        $hidden = $xpath->query('//input[@type="hidden"]');
        foreach ($hidden as $values) {
            if ($values->getAttribute("name") == 'affiliateid') {
                $this->_publisherID = $values->getAttribute("value");
            }
        }

        $results = $xpath->query('//*[contains(concat(" ", normalize-space(@id), " "), " oaNavigationTabs ")]');
        $finished = false;
        foreach ($results as $result) {
            $linkList = $result->getElementsByTagName('a');
            if ($linkList->length > 0) {
                $attrs = $linkList->item(0)->attributes;

                foreach ($attrs as $attrName => $attrNode) {
                    if (!$finished && $attrName = 'href') {
                        $parseUrl = trim($attrNode->nodeValue);
                        $parts = parse_url($parseUrl);
                        parse_str($parts['query'], $query);
                        $this->_windowid = $query['windowid'];
                        $this->_sessionIDCurl = $query['sessId'];
                        $finished = true;
                    }
                }
            }
        }

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
        $credentials[] = $parameter;

        return $credentials;
    }

    /**
     * @return mixed
     */
    public function checkConnection()
    {
        $postdata = http_build_query(
            [
                'class'  => 'Publisher',
                'method' => 'getPublisher',
                'val1'   => $this->_sessionID,
                'val2'   => $this->_publisherID,
                'val3'   => ''
            ]);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];
        $context = stream_context_create($opts);
        $result = unserialize(file_get_contents('http://www.tyroocentral.com/www/api/v2/xmlrpc/APICall.php', false, $context));
        $connection = $result[0];

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
        $obj['name'] = 'Tyroo';
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

        $postdata = http_build_query(
            [
                'class'  => 'Publisher',
                'method' => 'getPublisherDailyStatistics',
                'val1'   => $this->_sessionIDCurl,
                'val2'   => $this->_publisherID,
                'val3'   => $dStartDate->format("Y-m-d"),
                'val4'   => $dEndDate->format("Y-m-d"),
                'val5'   => 'Asia/Calcutta',
                'val6'   => ''
            ]);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];
        $context = stream_context_create($opts);
        $result = unserialize(file_get_contents('http://www.tyroocentral.com/www/api/v2/xmlrpc/APICall.php', false, $context));
        $json = json_encode($result);
        $transactionsList = json_decode($json, true);
        foreach ($transactionsList[1] as $transactionJson) {
            if ($transactionJson["revenue"] != 0) {
                $transaction = [];
                $transaction['merchantId'] = "1";
                $transaction['date'] = $transactionJson["day"];
                $transaction['amount'] = Utilities::parseDouble($transactionJson["revenue"]);
                $transaction['commission'] = Utilities::parseDouble($transactionJson["revenue"]);
                $transaction['status'] = Utilities::STATUS_CONFIRMED;
                $totalTransactions[] = $transaction;
            }
        }


        return $totalTransactions;
    }

}
