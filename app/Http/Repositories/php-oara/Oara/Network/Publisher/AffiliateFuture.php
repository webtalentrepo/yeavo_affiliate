<?php
namespace Oara\Network\Publisher;
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
 * @author     Carlos Morillo Merino - Updated by Paolo Nardini on 2019-Feb-05
 * @category   Af
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class AffiliateFuture extends \Oara\Network
{

    private $_client = null;
    private $_api_credentials = [];

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $user = $credentials['user'];
        $password = $credentials['password'];
        $this->_client = new \Oara\Curl\Access($credentials);

        if (isset($_ENV['AFFILIATE_FUTURE_API_KEY']) && isset($_ENV['AFFILIATE_FUTURE_API_PASSWORD'])) {
            // BV-883 - Api credential was passed by ENV
            $this->_api_credentials[] = new \Oara\Curl\Parameter('key', $_ENV['AFFILIATE_FUTURE_API_KEY']);
            $this->_api_credentials[] = new \Oara\Curl\Parameter('passcode', $_ENV['AFFILIATE_FUTURE_API_PASSWORD']);
            return;
        }

        // Try to scrape the login page
        $valuesLogin = array(
            new \Oara\Curl\Parameter('txtUsername', $user),
            new \Oara\Curl\Parameter('txtPassword', $password),
            new \Oara\Curl\Parameter('btnLogin', 'Login')
        );

        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://afuk.affiliate.affiliatefuture.co.uk/login.aspx?', $valuesLogin);
        $exportReport =  $this->_client->post($urls);

        $objDOM = new \DOMDocument();
        @$objDOM->loadHTML($exportReport[0]);
        $objXPath = new \DOMXPath($objDOM);
        $objInputs = $objXPath->query("//input[@type='hidden']");
        // Get all session values needed to login
        foreach ($objInputs as $objInput) {
            $valuesLogin[] = new \Oara\Curl\Parameter($objInput->getAttribute('name'), $objInput->getAttribute('value'));
        }
        // Now try again to log
        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://afuk.affiliate.affiliatefuture.co.uk/login.aspx?', $valuesLogin);
        $this->_client->post($urls);

        $this->_credentials = $credentials;
    }

    /**
     * @return array
     */
    public function getNeededCredentials()
    {
        $credentials = array();

        $parameter = array();
        $parameter["description"] = "User Log in";
        $parameter["required"] = true;
        $parameter["name"] = "User";
        $credentials["user"] = $parameter;

        $parameter = array();
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
        if (count($this->_api_credentials) > 1 &&  $this->_api_credentials[0]->getKey() == 'key' && $this->_api_credentials[1]->getKey() == 'passcode') {
            // BV-883 - Api credential was passed by ENV
            return true;
        }

        //If not login properly the construct launch an exception
        $urls = array();
        // $urls[] = new \Oara\Curl\Request('https://affiliates.affiliatefuture.com/myaccount/invoices.aspx', array());
        $urls[] = new \Oara\Curl\Request('https://afuk.affiliate.affiliatefuture.co.uk/reporting/ReportingAPIs.aspx', array());
        $result = $this->_client->get($urls);

        $this->_api_credentials = array();
        $objDOM = new \DOMDocument();
        @$objDOM->loadHTML($result[0]);
        $objXPath = new \DOMXPath($objDOM);
        $objInputs = $objXPath->query("//input[@type='text']");
        if ($objInputs->length > 0) {
            foreach ($objInputs as $objInput) {
                $key = $objInput->getAttribute('name');
                $value = $objInput->getAttribute('value');
                if ($key == 'APIkey') {
                    $key = 'key';
                }
                elseif ($key == 'APIpassword') {
                    $key = 'passcode';
                }
                $this->_api_credentials[] = new \Oara\Curl\Parameter($key, $value);
            }
        }
        else {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = Array();
        $merchantExportList = self::readMerchants();
        foreach ($merchantExportList as $merchant) {
            $obj = Array();
            $obj['cid'] = $merchant['cid'];
            $obj['name'] = $merchant['name'];
            $obj['description'] = $merchant['description'];
            $obj['url'] = $merchant['url'];
            $obj['joined'] = $merchant['joined'];
            $merchants[] = $obj;
        }
        return $merchants;
    }

    /**
     * @return array
     */
    public function readMerchants()
    {
        $merchantList = array();

        // STEP 1 - GET ALL MERCHANTS

        $parameters = array_merge($this->_api_credentials, array(
            new \Oara\Curl\Parameter('merchantsJoined', 'ALL'),
            new \Oara\Curl\Parameter('newMerchants', 'NO')
        ));

        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://api.affiliatefuture.com/PublisherService.svc/GetAFMerchantList?', $parameters);
        $xmlReport = $this->_client->get($urls);
        $xml = \simplexml_load_string($xmlReport[0], null, LIBXML_NOERROR | LIBXML_NOWARNING);

        foreach($xml as $key => $value) {
            if ($key == 'Merchant') {
                $merchant = array();
                $merchant['name'] = '' . $value->MerchantName;
                $merchant['description'] = '' . $value->SiteDescription;
                $merchant['cid'] = '' . $value->MerchantSiteId;
                $merchant['url'] = '' . $value->SiteAddress;
                $merchant['joined'] = '' . $value->MerchantJoined;
                $merchantList[] = $merchant;
            }
        }

        // STEP 2 GET ONLY NEW MERCHANTS
        $parameters = array_merge($this->_api_credentials, array(
            new \Oara\Curl\Parameter('merchantsJoined', 'ALL'),
            new \Oara\Curl\Parameter('newMerchants', 'YES')
        ));

        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://api.affiliatefuture.com/PublisherService.svc/GetAFMerchantList?', $parameters);
        $xmlReport = $this->_client->get($urls);
        $xml = \simplexml_load_string($xmlReport[0], null, LIBXML_NOERROR | LIBXML_NOWARNING);

        foreach($xml as $key => $value) {
            if ($key == 'Merchant') {
                $merchant = array();
                $merchant['name'] = '' . $value->MerchantName;
                $merchant['description'] = '' . $value->SiteDescription;
                $merchant['cid'] = '' . $value->MerchantSiteId;
                $merchant['url'] = '' . $value->SiteAddress;
                $merchant['joined'] = '' . $value->MerchantJoined;
                $merchantList[] = $merchant;
            }
        }

        return $merchantList;
    }

    /**
     * @param $html
     * @return array
     */
    private function htmlToCsv($html)
    {
        $html = str_replace(array(
            "\t",
            "\r",
            "\n"
        ), "", $html);
        $csv = "";

        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        $results = $xpath->query('//tr');
        foreach ($results as $result) {

            $doc = new \DOMDocument();
            @$doc->loadHTML(\Oara\Utilities::DOMinnerHTML($result));
            $xpath = new \DOMXPath($doc);
            $resultsTd = $xpath->query('//td');
            $countTd = $resultsTd->length;
            $i = 0;
            foreach ($resultsTd as $resultTd) {
                $value = $resultTd->nodeValue;

                $doc = new \DOMDocument();
                @$doc->loadHTML(\Oara\Utilities::DOMinnerHTML($resultTd));
                $xpath = new \DOMXPath($doc);
                $resultsA = $xpath->query('//a');
                foreach ($resultsA as $resultA) {
                    $value = $resultA->getAttribute("href");
                }

                if ($i != $countTd - 1) {
                    $csv .= \trim($value) . ";";
                } else {
                    $csv .= \trim($value);
                }
                $i++;
            }
            $csv .= "\n";
        }
        $exportData = \str_getcsv($csv, "\n");
        return $exportData;
    }

    /**
     * @param null $merchantList
     * @param \DateTime|null $dStartDate
     * @param \DateTime|null $dEndDate
     * @return array
     * @throws \Exception
     */
    public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
    {
        $merchantIdMap = \Oara\Utilities::getMerchantIdMapFromMerchantList($merchantList);

        $nowDate = new \DateTime();
        $dStartDate = clone $dStartDate;
        $dStartDate->setTime(0,0,0);
        $dEndDate = clone $dEndDate;
        $dEndDate->setTime(23,59,59);

        $parameters = array_merge($this->_api_credentials, array(
            new \Oara\Curl\Parameter('startDate', $dStartDate->format("d-M-Y")),
            new \Oara\Curl\Parameter('endDate', $dEndDate->format("d-M-Y"))
        ));

        $transactions = Array();
        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://api.affiliatefuture.com/PublisherService.svc/GetTransactionListbyDate?', $parameters);
        $urls[] = new \Oara\Curl\Request('https://api.affiliatefuture.com/PublisherService.svc/GetCancelledTransactionListbyDate?', $parameters);
        $xmlReport = $this->_client->get($urls);
        for ($i = 0; $i < \count($urls); $i++) {
            $xml = \simplexml_load_string($xmlReport[$i], null, LIBXML_NOERROR | LIBXML_NOWARNING);
            if (isset($xml->error)) {
                throw new \Exception('[AffiliateFuture][GetTransactionList] XML Error connecting to the server');
            }
            if (isset($xml->TransactionList)) {
                foreach ($xml->TransactionList as $transaction) {
                    $date = new \DateTime(self::findAttribute($transaction, 'TransactionDate'));

                    if (count($merchantIdMap)== 0 || isset($merchantIdMap[(int)self::findAttribute($transaction, 'MerchantID')]) &&
                        ($date->format("Y-m-d H:i:s") >= $dStartDate->format("Y-m-d H:i:s")) &&
                        ($date->format("Y-m-d H:i:s") <= $dEndDate->format("Y-m-d H:i:s"))) {

                        $obj = Array();
                        $obj['currency'] = 'GBP'; // Affiliate Future doesn't handle currencies, default is Pound!
                        $obj['merchantId'] = self::findAttribute($transaction, 'MerchantID');
                        $obj['date'] = $date->format("Y-m-d H:i:s");
                        if (self::findAttribute($transaction, 'TrackingReference') != null) {
                            $obj['custom_id'] = self::findAttribute($transaction, 'TrackingReference');
                        }
                        $obj['unique_id'] = self::findAttribute($transaction, 'TransactionID');
                        if ($i == 0) {
                            // From FAQ: Merchants don’t validate sales. A merchant has 5 days after a sale has been made to cancel any invalid, fraudulent or cancelled transactions.
                            // After this time has elapsed, the merchant can no longer cancel the transaction and the commission will be yours.
                            $interval = $date->diff($nowDate);
                            if ($interval->format('%a') > 5) {
                                $obj['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                            } else {
                                $obj['status'] = \Oara\Utilities::STATUS_PENDING;
                            }
                        } else {
                            if ($i == 1) {
                                $obj['status'] = \Oara\Utilities::STATUS_DECLINED;
                            }
                        }
                        $obj['amount'] = \Oara\Utilities::parseDouble(self::findAttribute($transaction, 'SaleValue'));
                        $obj['commission'] = \Oara\Utilities::parseDouble(self::findAttribute($transaction, 'SaleCommission'));
                        $leadCommission = \Oara\Utilities::parseDouble(self::findAttribute($transaction, 'LeadCommission'));
                        if ($leadCommission != 0) {
                            $obj['commission'] += $leadCommission;
                        }
                        $transactions[] = $obj;
                    }
                }
            }
        }
        return $transactions;
    }

    /**
     * @param null $object
     * @param null $attribute
     * @return null|string
     */
    private function findAttribute($object = null, $attribute = null)
    {
        $return = null;
        $return = trim($object->$attribute);
        return $return;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPaymentHistory()
    {
        $paymentHistory = array();
        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://affiliates.affiliatefuture.com/myaccount/invoices.aspx', array());
        $exportReport = $this->_client->get($urls);

        $doc = new \DOMDocument();
        @$doc->loadHTML($exportReport[0]);
        $xpath = new \DOMXPath($doc);
        $tableList = $xpath->query('//table');
        $registerTable = $tableList->item(12);
        if ($registerTable == null) {
            throw new \Exception('Fail getting the payment History');
        }
        $registerLines = $registerTable->childNodes;
        for ($i = 1; $i < $registerLines->length; $i++) {
            $registerLine = $registerLines->item($i)->childNodes;
            $obj = array();
            $date = \DateTime::createFromFormat("d/m/Y", trim($registerLine->item(1)->nodeValue));
            $date->setTime(0, 0);
            $obj['date'] = $date->format("Y-m-d H:i:s");
            $obj['pid'] = trim($registerLine->item(0)->nodeValue);
            $value = trim(substr(trim($registerLine->item(4)->nodeValue), 4));
            $obj['value'] = \Oara\Utilities::parseDouble($value);
            $obj['method'] = 'BACS';
            $paymentHistory[] = $obj;
        }

        return $paymentHistory;
    }
}
