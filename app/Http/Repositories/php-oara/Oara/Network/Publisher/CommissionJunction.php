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
 * @author     Carlos Morillo Merino
 * @category   Cj
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class CommissionJunction extends \Oara\Network
{
    private $_client = null;
    private $_memberId = null;
    private $_accountId = null;
    private $_apiPassword = null;
    protected $_sitesAllowed = array ();
    private $_requestor_cid = null;


    /*
     * ATTENTION - IMPORTANT UPDATES - 2019-03-22 by <PN>
     * CJ REST API is now DEPRECATED and will be removed on June 1, 2019
     * In the meanwhile, old functions calls using the previously generated DEVELOPER_KEYs in the header continue working,
     * but for new created accounts you cannot generate a new developer key, but only a PERSONAL ACCESS TOKEN.
     * REST API could use the Personal Access Tokens by sending it in the header as "Authorization: Bearer XXXXXXX ... "
     * The api call need a NEW MANDATORY PARAMETER called "requestor-cid" that represent the COMPANY ID in the CJ account dashboard.
     *
     * CHANGES TO THIS CLASS:
     * - $this->_website_id (not used) is now replaced by $this->_requestor_cid
     * - If $credentials['id_site'] is passed to login() and it's not empty, it will be used in the Api function calls, and the
     *   $credentials['apipassword'] will be used as a Personal Access Token and sent in header as "Authorization: Bearer xxxx..."
     * - If $credentials['id_site'] is missing or empty, it will not be used, and the $credentials['apipassword'] will be
     *   used as a Developer Key and sent in header as simple "Authorization: xxxx..."
     */

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_apiPassword = @$credentials['apipassword'];
        $this->_requestor_cid = @$credentials['id_site'];
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
        $parameter["description"] = "API Password ";
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
        $connection = true;

        $result = self::apiCall('https://commission-detail.api.cj.com/v3/commissions?date-type=event');
        if ($result===false || \preg_match("/error/", $result)) {
            return false;
        }

        return $connection;
    }

    private function apiCall($url)
    {
        $ch = curl_init();
        if (!empty($this->_requestor_cid)) {
            if (strpos($url, 'requestor-cid') === false) {
                // 2019-03-22 <PN> see notes on top of this source
                // Add cid parameter to url
                $pos = strpos($url, '?');
                if ($pos === false) {
                    // The only parameter
                    $url = $url . '?requestor-cid=' . $this->_requestor_cid;
                }
                else {
                    // Prepend to first parameter
                    $url = substr($url,0, $pos+1) . 'requestor-cid=' . $this->_requestor_cid . '&' . substr($url,$pos + 1);
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if (!empty($this->_requestor_cid)) {
            // 2019-03-22 <PN> see notes on top of this source
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->_apiPassword));
        }
        else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: " . $this->_apiPassword));
        }

        $curl_results = curl_exec($ch);
        curl_close($ch);
        return $curl_results;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = array();
        $merchantsExport = self::getMerchantExport();
        foreach ($merchantsExport as $merchantData) {
            $obj = Array();
            $obj['cid'] = $merchantData[0];
            $obj['name'] = $merchantData[1];
            // Added more info - 2018-04-23 <PN>
            $obj['status'] = $merchantData[2];
            $obj['relationship_status'] = $merchantData[3];
            $obj['url'] = $merchantData[4];
            $merchants[] = $obj;
        }
        return $merchants;
    }

    /**
     * @return array
     */
    private function getMerchantExport()
    {
        $merchantReportList = array();
        /*$valuesFromExport = array(new \Oara\Curl\Parameter('sortKey', 'active_start_date'),
            new \Oara\Curl\Parameter('sortOrder', 'DESC'),
            new \Oara\Curl\Parameter('contractView', 'ALL'),
            new \Oara\Curl\Parameter('contractView', 'ALL'),
            new \Oara\Curl\Parameter('format', '6'),
            new \Oara\Curl\Parameter('contractState', 'active'),
            new \Oara\Curl\Parameter('column', 'merchantid'),
            new \Oara\Curl\Parameter('column', 'websitename'),
            new \Oara\Curl\Parameter('column', 'merchantcategory')
        );

        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://members.cj.com/member/' . $this->_memberId . '/publisher/accounts/listmyadvertisers.do', array());
        $exportReport = $this->_client->get($urls);

        if (!preg_match('/Sorry, No Results Found\./', $exportReport[0], $matches)) {
            $urls = array();
            $urls[] = new \Oara\Curl\Request('https://members.cj.com/member/' . $this->_memberId . '/publisher/accounts/listmyadvertisers.do', $valuesFromExport);
            $exportReport = $this->_client->post($urls);
            $exportData = str_getcsv($exportReport[0], "\n");
            $merchantReportList = Array();
            $num = count($exportData);
            for ($i = 1; $i < $num; $i++) {
                $merchantExportArray = str_getcsv($exportData[$i], ",");
                $merchantReportList[] = $merchantExportArray;
            }
        }*/
        $page=1;
        $per_page = 100;
        $total_pages = 99;
        do{
            if ($page > $total_pages){
                exit;
            }
            // Get All programs even if not active - 2018-04-23 <PN>
            $response = self::apiCall('https://advertiser-lookup.api.cj.com/v3/advertiser-lookup?advertiser-ids=&records-per-page='.$per_page.'&page-number='.$page);
            $xml = \simplexml_load_string($response, null, LIBXML_NOERROR | LIBXML_NOWARNING);
            if (!isset($xml->advertisers)) {
                break;
            }

            $total_adv=(int)$xml->advertisers[0]['total-matched'];
            $total_pages=ceil($total_adv/$per_page);
            foreach ($xml->advertisers->advertiser as $adv){
                $adv_id='';
                $adv_name='';
                foreach ($adv->children() AS $key=>$value){

                    if ($key=='advertiser-id'){
                        $adv_id=(string)$value;
                    }
                    if ($key=='advertiser-name'){
                        $adv_name=(string)$value;
                    }
                    // Added more info - 2018-04-23 <PN>
                    if ($key=='account-status'){
                        $adv_status=(string)$value;
                    }
                    if ($key=='relationship-status'){
                        $adv_relationship_status=(string)$value;
                    }
                    if ($key=='program-url'){
                        $adv_url=(string)$value;
                    }
                }
                if (trim($adv_id)!='' && trim($adv_name)!=''){
                    $merchantReportList[]=[
                        $adv_id,
                        $adv_name,
                        $adv_status,
                        $adv_relationship_status,
                        $adv_url,
                    ] ;
                }

            }
            $page++;

        }while($total_pages>=$page);


        return $merchantReportList;
    }

    /**
     * @param null $merchantList
     * @param \DateTime|null $dStartDate
     * @param \DateTime|null $dEndDate
     * @return array
     * @throws Exception
     */
    public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
    {
        $totalTransactions = Array();
        if (!is_null($merchantList) && is_array($merchantList) && count($merchantList) > 0) {
            $merchantIdArray = \array_keys(\Oara\Utilities::getMerchantIdMapFromMerchantList($merchantList));
            $iteration = self::calculeIterationNumber(\count($merchantIdArray), '20');
            $byMerchant = true;
        }
        else {
            $iteration = 1;
            $byMerchant = false;
        }
        for ($it = 0; $it < $iteration; $it++) {
            try {
                $transactionDateEnd = clone $dEndDate;
                $transactionDateEnd->add(new \DateInterval('P1D'));
                if ($byMerchant) {
                    // Only selected merchants
                    $merchantSlice = \array_slice($merchantIdArray, $it * 20, 20);
                    $restUrl = 'https://commission-detail.api.cj.com/v3/commissions?cids=' . \implode(',', $merchantSlice) . '&date-type=posting&start-date=' . $dStartDate->format("Y-m-d") . '&end-date=' . $transactionDateEnd->format("Y-m-d");
                }
                else {
                    // All merchants
                    $restUrl = 'https://commission-detail.api.cj.com/v3/commissions?date-type=posting&start-date=' . $dStartDate->format("Y-m-d") . '&end-date=' . $transactionDateEnd->format("Y-m-d");
                }
                $totalTransactions = \array_merge($totalTransactions, self::getTransactionsXml($restUrl, $merchantList));
            } catch (\Exception $e) {
                $amountDays = $dStartDate->diff($dEndDate)->days;
                $auxDate = clone $dStartDate;
                for ($j = 0; $j < $amountDays; $j++) {
                    $transactionDateEnd = clone $auxDate;
                    $transactionDateEnd->add(new \DateInterval('P1D'));
                    $restUrl = 'https://commission-detail.api.cj.com/v3/commissions?cids=' . \implode(',', $merchantSlice) . '&date-type=posting&start-date=' . $auxDate->format("Y-m-d") . '&end-date=' . $transactionDateEnd->format("Y-m-d");
                    try {
                        $totalTransactions = \array_merge($totalTransactions, self::getTransactionsXml($restUrl, $merchantList));
                    } catch (\Exception $e) {
                        $try = 0;
                        $done = false;
                        while (!$done && $try < 5) {
                            try {
                                $totalTransactions = \array_merge($totalTransactions, self::transactionsByType(\implode(',', $merchantSlice), $auxDate, $transactionDateEnd, $merchantList));
                                $done = true;
                            } catch (\Exception $e) {
                                $try++;
                                //echo "try again $try\n\n";
                            }
                        }
                        if ($try == 5) {
                            throw new \Exception("Couldn't get data from the Transaction");
                        }
                    }
                    $auxDate->add(new \DateInterval('P1D'));
                }
            }
        }
        return $totalTransactions;
    }

    /**
     * @param $rowAvailable
     * @param $rowsReturned
     * @return int
     */
    private function calculeIterationNumber($rowAvailable, $rowsReturned)
    {
        $iterationDouble = (double)($rowAvailable / $rowsReturned);
        $iterationInt = (int)($rowAvailable / $rowsReturned);
        if ($iterationDouble > $iterationInt) {
            $iterationInt++;
        }
        return $iterationInt;
    }

    /**
     * @param $restUrl
     * @param $merchantList
     * @return array
     */
    private function getTransactionsXml($restUrl, $merchantList)
    {
        $totalTransactions = array();
        $merchantIdList = \Oara\Utilities::getMerchantIdMapFromMerchantList($merchantList);
        $response = self::apiCall($restUrl);
        $xml = \simplexml_load_string($response, null, LIBXML_NOERROR | LIBXML_NOWARNING);
        if (isset($xml->commissions->commission)) {
            foreach ($xml->commissions->commission as $singleTransaction) {

                if (\count($this->_sitesAllowed) == 0 || \in_array(( int )self::findAttribute($singleTransaction, 'website-id'), $this->_sitesAllowed)) {

                    if (count($merchantIdList) == 0 || isset($merchantIdList[(int)self::findAttribute($singleTransaction, 'cid')])) {

                        $transaction = Array();
                        $transaction ['unique_id'] = self::findAttribute($singleTransaction, 'commission-id');//self::findAttribute($singleTransaction, 'original-action-id');
                        $transaction ['action'] = self::findAttribute($singleTransaction, 'action-type');
                        $transaction['merchantId'] = self::findAttribute($singleTransaction, 'cid');
                        //event-date - The associated event date for the item in UTC time zone.
                        $transactionDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", (self::findAttribute($singleTransaction, 'event-date')));
                        $transaction['date'] = $transactionDate->format("Y-m-d H:i:sO");
                        $transaction['custom_id'] = '';
                        if (self::findAttribute($singleTransaction, 'sid') != null) {
                            $transaction['custom_id'] = self::findAttribute($singleTransaction, 'sid');
                        }

                        $transaction ['amount'] = \Oara\Utilities::parseDouble(self::findAttribute($singleTransaction, 'sale-amount'));
                        $transaction ['commission'] = \Oara\Utilities::parseDouble(self::findAttribute($singleTransaction, 'commission-amount'));

                        if (self::findAttribute($singleTransaction, 'action-status') == 'locked' || self::findAttribute($singleTransaction, 'action-status') == 'closed') {
                            $transaction ['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                        } else if (self::findAttribute($singleTransaction, 'action-status') == 'extended' || self::findAttribute($singleTransaction, 'action-status') == 'new') {
                            $transaction ['status'] = \Oara\Utilities::STATUS_PENDING;
                        } else if (self::findAttribute($singleTransaction, 'action-status') == 'corrected') {
                            $transaction ['status'] = \Oara\Utilities::STATUS_DECLINED;
                        }

                        if ($transaction ['commission'] == 0) {
                            $transaction ['status'] = \Oara\Utilities::STATUS_PENDING;
                        }

                        /*
                        // Negative commission must be subtracted by original commission identified by the same 'original-action-id' field - 2018-07-13 <PN>
                        // Only if result is zero the commission could be set DECLINED. This logic must be implemented by the caller!
                        if ($transaction ['amount'] < 0 || $transaction ['commission'] < 0) {
                            $transaction ['status'] = \Oara\Utilities::STATUS_DECLINED;
                            $transaction ['amount'] = \abs($transaction ['amount']);
                            $transaction ['commission'] = \abs($transaction ['commission']);
                        }
                        */
                        $transaction ['aid'] = self::findAttribute($singleTransaction, 'aid');
                        $transaction ['order-id'] = self::findAttribute($singleTransaction, 'order-id');
                        $transaction ['original'] = (self::findAttribute($singleTransaction, 'original') === 'true');
                        // 'original-action-id' is used as reference field between original commission and adjust/correction commission - 2018-07-13 <PN>
                        $transaction ['original-action-id'] = self::findAttribute($singleTransaction, 'original-action-id');
                        $totalTransactions[] = $transaction;
                    }
                }
            }
        }
        else {
            if ($xml->count() > 0) {
                if (isset($xml->title)) {
                    echo "[ERROR][CJ] " . (string) $xml->title . PHP_EOL;
                }
                foreach($xml->children() as $child) {
                    $key = $child->getName();
                    $value = $child->attributes();
                    echo "[WARNING][CJ] " . $key .  ": " . (string) $value . PHP_EOL;
                    foreach ($child->children() AS $subkey => $value) {
                        echo "[WARNING][CJ] " . $subkey .  ": " . (string) $value . PHP_EOL;
                    }
                }
            }
        }
        return $totalTransactions;
    }

    /**
     * @param null $object
     * @param null $attribute
     * @return null|string
     */
    private function findAttribute($object = null, $attribute = null)
    {
        $return = null;
        $return = \trim($object->$attribute);
        return $return;
    }

    /**
     * @param $cid
     * @param $startDate
     * @param $endDate
     * @param $merchantList
     * @return array
     */
    private function transactionsByType($cid, $startDate, $endDate, $merchantList)
    {
        $totalTransactions = array();
        $typeTransactions = array("bonus", "click", "impression", "sale", "lead", "advanced%20sale", "advanced%20lead", "performance%20incentive");
        foreach ($typeTransactions as $type) {
            $restUrl = 'https://commission-detail.api.cj.com/v3/commissions?action-types=' . $type . '&cids=' . $cid . '&date-type=posting&start-date=' . $startDate->format("Y-m-d") . '&end-date=' . $endDate->format("Y-m-d");
            $totalTransactions = \array_merge($totalTransactions, self::getTransactionsXml($restUrl, $merchantList));
        }
        return $totalTransactions;
    }

    /**
     * @param $pid
     * @param null $merchantList
     * @param null $startDate
     * @return array
     */
    public function paymentTransactions($pid)
    {
        $transactionList = array();
        $invoices = $this->getPaymentHistory();
        for ($i = 0; $i < \count($invoices); $i++) {
            if ($invoices[$i]['pid'] == $pid) {
                $endDate = $invoices[$i]['date'];
                if (isset($invoices[$i + 1])) {
                    $startDate = $invoices[$i + 1]['date'];
                } else {
                    $startDate = \date("Y-m-d", \strtotime($invoices[i]['date']) - (90 * 60 * 60 * 24));
                }
                break;
            }
        }
        $startDate = \date("Y-m-d", \strtotime($startDate));
        $endDate = \date("Y-m-d", \strtotime($endDate));
        $exportReport = $this->_client->get(array(new \Oara\Curl\Request('https://members.cj.com/member/publisher/' . $this->_accountId . '/transactionReport.json?startDate=' . $startDate . '&endDate=' . $endDate . '&allowAllDateRanges=true&columnSort=amount%09DESC&startRow=1&endRow=1000', array())));
        $advertiserPaymentIds = array();
        foreach (\json_decode($exportReport[0])->{'records'}->{'record'} as $advertiser) {
            if (($advertiser->{'advertiserId'} != '-3') && (!in_array($advertiser->{'txnId'}, $advertiserPaymentIds))) {
                $advertiserPaymentIds[] = $advertiser->{'txnId'};
            }
        }
        foreach ($advertiserPaymentIds as $id) {
            $exportReport = $this->_client->get(array(new \Oara\Curl\Request('https://members.cj.com/member/publisher/' . $this->_accountId . '/commissionReport/detailForTransactionId.json?allowAllDateRanges=true&txnId=' . $id . '&columnSort=publisherCommission%09DESC&startRow=1&endRow=1000', array())));
            $transactions = \json_decode($exportReport[0])->{'records'}->{'record'};
            if (!isset($transactions->{'advertiserId'})) {
                foreach ($transactions as $transaction) {
                    $transactionList[] = $transaction->{'commissionId'};
                }
            } else {
                $transactionList[] = $transactions->{'commissionId'};
            }
        }
        return $transactionList;
    }

    /**
     * @return array
     */
    public function getPaymentHistory()
    {
        $paymentHistory = array();
        $urls = array();
        $urls[] = new \Oara\Curl\Request('https://members.cj.com/member/cj/publisher/paymentStatus', array());
        $exportReport = $this->_client->get($urls);
        if (\preg_match('/\/publisher\/getpublisherpaymenthistory\.do/', $exportReport[0], $matches)) {
            $urls = array();
            $valuesFromExport = array(new \Oara\Curl\Parameter('startRow', '0'),
                new \Oara\Curl\Parameter('sortKey', ''),
                new \Oara\Curl\Parameter('sortOrder', ''),
                new \Oara\Curl\Parameter('format', '6'),
                new \Oara\Curl\Parameter('button', 'Go')
            );
            $urls[] = new \Oara\Curl\Request('https://members.cj.com/member/' . $this->_memberId . '/publisher/getpublisherpaymenthistory.do?', $valuesFromExport);
            $exportReport = $this->_client->get($urls);
            $exportData = \str_getcsv($exportReport[0], "\n");
            $num = \count($exportData);
            for ($j = 1; $j < $num; $j++) {
                $paymentData = \str_getcsv($exportData[$j], ",");
                $obj = array();
                $date = \DateTime::createFromFormat("d-M-Y H:i \P\S\T", $paymentData[0]);
                if (!$date) {
                    $date = \DateTime::createFromFormat("d-M-Y H:i \P\D\T", $paymentData[0]);
                }
                $obj['date'] = $date->format("Y-m-d H:i:s");
                $obj['value'] = \Oara\Utilities::parseDouble($paymentData[1]);
                $obj['method'] = $paymentData[2];
                $obj['pid'] = $paymentData[6];
                $paymentHistory[] = $obj;
            }
        }
        return $paymentHistory;
    }

    /**
     * @param string $idSite
     */
    public function addAllowedSite(string $idSite){
        if (!in_array($idSite, $this->_sitesAllowed)){
            $this->_sitesAllowed[]=$idSite;
        }
    }
}
