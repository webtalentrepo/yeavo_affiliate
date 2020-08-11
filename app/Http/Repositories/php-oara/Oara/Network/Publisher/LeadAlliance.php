<?php

namespace Oara\Network\Publisher;
/**
 * The goal of the Open Affiliate Report Aggregator (OARA) is to develop a set
 * of PHP classes that can download affiliate reports from a number of affiliate networks, and store the data in a common format.
 **/

/**
 * Export Class
 *
 * @author     Paolo Nardini
 * @category   LeadAlliance
 * @version    Release: 01.00
 *
 */
class LeadAlliance extends \Oara\Network
{

	private $_credentials = null;

	/**
	 * @param $credentials
	 */
	public function login($credentials)
	{
		$this->_credentials = $credentials;
	}

	/**
	 * Check the connection
	 */
	public function checkConnection()
	{
	    // Don't check connection ... just check for valid api keys
        if (!isset($_ENV['LEAD_ALLIANCE_PUBLIC']) && !isset($_ENV['LEAD_ALLIANCE_PRIVATE'])) {
            return false;
        }
        return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see library/Oara/Network/Interface#getMerchantList()
	 */
	public function getMerchantList()
	{
        // NOT IMPLEMENTED YET
        $merchants = array();
		return $merchants;
	}

	/**
	 * @param null $merchantList array of merchants id to retrieve transactions (empty array or null = all merchants)
	 * @param \DateTime|null $dStartDate
	 * @param \DateTime|null $dEndDate
	 * @return array
	 * @throws \Exception
	 */
	public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
	{
		$totalTransactions = array();

		// $merchantIdList = \Oara\Utilities::getMerchantIdMapFromMerchantList($merchantList);

		$user = $this->_credentials['user'];
		$password = $this->_credentials['password'];

        // id_site could be used to pass custom api url (white labels merchants using LeadAlliance API)
        // ... example QVC use "https://partner.qvc.de"
		$id_site = $this->_credentials['id_site'];

        if (!empty($id_site) && strpos($id_site, 'http') !== false) {
            // The API custom url is passed with id_site parameter
            $url_endpoint = $id_site;
            $id_site = '';
        }
        else {
            $url_endpoint = 'https://www.lead-alliance.net';
        }
        if (substr($url_endpoint,-1,1) == '/') {
            // strip trailing slash from url
            $url_endpoint = substr($url_endpoint,0,-1);
        }

        $public = '';
        $hash = '';

        if (isset($_ENV['LEAD_ALLIANCE_PUBLIC'])) {
            $public = $_ENV['LEAD_ALLIANCE_PUBLIC'];
        }
        if (isset($_ENV['LEAD_ALLIANCE_PRIVATE'])) {
            $private = $_ENV['LEAD_ALLIANCE_PRIVATE'];
            $hash = hash_hmac('sha256', '', $private);
        }


        $url = $url_endpoint . "/api/v1/index.php/partner/transactions?date=" . $dStartDate->format("Y-m-d") . "&dateend=" . $dEndDate->format("Y-m-d");
        if (!empty($id_site)) {
            // id_site is a specific partner id
            $url .= "&prid=" . $id_site;
        }
        // initialize curl resource
        $ch = curl_init();
        // set the http request authentication headers
        $headers = array(
            'Authorization: Basic ' . base64_encode($user . ':' . $password),
            'Content-Type:application/json',
            'lea-Public:' . $public,
            'lea-hash:' . $hash,
        );
        // set curl options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // execute curl
        $response = curl_exec($ch);
        if (!empty($response)) {
            if (stripos($response,'error') !== false) {
                echo "[LeadAlliance][getTransactionList] Error: " . $response ."\n ";
                return $totalTransactions;
            }
        }
        $transactionList = json_decode($response, true);
        if (is_array($transactionList) && count($transactionList) > 0) {
            foreach ($transactionList as $transaction) {
                $transactionArray = Array();
                $transactionArray['unique_id'] = $transaction['transactionid'];
                $transactionArray['merchantId'] = $transaction['programid'];
                $transactionArray['merchantName'] = $transaction['program'];
                $date_origin = $transaction['dateorigin'];
                $time_origin = $transaction['timeorigin'];
                $transactionArray['date'] = $date_origin . ' ' . $time_origin;
                $transactionArray['click_date'] = $transaction['timeclick'];
                $transactionArray['update_date'] = $transaction['dateedit'];
                $transactionArray['custom_id'] = $transaction['subid'];
                if ($transaction['status'] == '2') {
                    $transactionArray['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                } elseif ($transaction['status'] == '1') {
                    $transactionArray['status'] = \Oara\Utilities::STATUS_PENDING;
                } elseif ($transaction['status'] == '0') {
                    $transactionArray['status'] = \Oara\Utilities::STATUS_DECLINED;
                } else {
                    throw new \Exception("Unexpected transaction status {$transaction['status']}");
                }
                $transactionArray['currency'] = 'EUR';  // Default value
                $transactionArray['amount'] = \Oara\Utilities::parseDouble($transaction['value']);
                $transactionArray['commission'] = \Oara\Utilities::parseDouble($transaction['commission']);
                $transactionArray['info'] = $transaction['info'];
                $transactionArray['statuscomment'] = $transaction['statuscomment'];
                $transactionArray['datepayment'] = $transaction['datepayment'];
                $transactionArray['category'] = $transaction['category'];
                $transactionArray['leadtype'] = $transaction['leadtype'];
                $transactionArray['adspaceid'] = $transaction['adspaceid'];
                $transactionArray['autookdate'] = $transaction['autookdate'];
                $totalTransactions[] = $transactionArray;
            }
        }
		return $totalTransactions;
	}

	/**
	 * See: https://developers.daisycon.com/api/resources/publisher-resources/
	 * @return array
	 */
	public function getVouchers()
	{
        throw new \Exception("Not implemented yet");
	}

}
