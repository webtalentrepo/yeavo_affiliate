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
 * @author     Carlos Morillo Merino (adapted by Paolo Nardini)
 * @category   ImpactRadius (Refactoring of Smg.php)
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class ImpactRadius extends \Oara\Network
{
	private $_credentials = null;
	private $_accountSid = null;
	private $_authToken = null;
	private $_merchant_list = null;


	/**
	 * @param $credentials
	 * @throws Exception
	 * @throws \Exception
	 * @throws \Oara\Curl\Exception
	 */
	public function login($credentials)
	{
		$this->_credentials = $credentials;

		if (isset($this->_credentials['api-sid']) && isset($this->_credentials['api-token'])) {
			// <slawn> - Allow passing sid+token with credentials
			$this->_accountSid = $this->_credentials['api-sid'];
			$this->_authToken = $this->_credentials['api-token'];
			return;
		}
		// Compatibility fallback - try to simulate login to access token
		$this->_client = new \Oara\Curl\Access($credentials);
		$user = $this->_credentials['user'];
		$password = $this->_credentials['password'];
		$loginUrl = 'https://app.impact.com/secure/login.user';

		$valuesLogin = array(new \Oara\Curl\Parameter('j_username', $user),
			new \Oara\Curl\Parameter('j_password', $password)
		);

		// Perform Login
		$urls = array();
		$urls[] = new \Oara\Curl\Request($loginUrl, $valuesLogin);
		$this->_client->post($urls);

		// Open Technical Settings page
		$urls = array();
		$urls[] = new \Oara\Curl\Request('https://app.impact.com/secure/mediapartner/accountSettings/mp-wsapi-flow.ihtml?', array());
		$apiPageHTML = $this->_client->get($urls);

		// Parse page content
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($apiPageHTML[0]);

		// Search for API keys
		$xpath = new \DOMXPath($dom);
		$contents = $xpath->query("//div[@class='uitkFields']/*");
		if ($contents->length != 0) {
			foreach ($contents as $i => $content) {
				$value = $content->textContent;
				if ($i == 0) {
					$this->_accountSid = str_replace(array("\n", "\t", " "), "", $value);
				} else if ($i == 1) {
					$this->_authToken = str_replace(array("\n", "\t", " "), "", $value);
				}
			}
		}
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
		if (empty($this->_accountSid) || empty($this->_authToken)) {
			return false;
		}
		return true;
	}

	/**
	 * It returns an array with the different merchants
	 * @return array|null
	 * @throws \Exception
	 */
	public function getMerchantList()
	{
		if (!is_null($this->_merchant_list) && is_array($this->_merchant_list)) {
			return $this->_merchant_list;
		}
		$this->_merchant_list = Array();
		if (!$this->checkConnection()) {
			$this->_merchant_list;
		}
		$url_params = "/Mediapartners/" . $this->_accountSid . "/Campaigns";
		$res = $this->makeCall($url_params);
		$currentPage = (int)$res->Campaigns->attributes()->page;
		$pageNumber = (int)$res->Campaigns->attributes()->numpages;
		while ($currentPage <= $pageNumber) {

			foreach ($res->Campaigns->Campaign as $campaign) {
				$obj = Array();
				$obj['cid'] = (int)$campaign->CampaignId;
				$obj['name'] = (string)$campaign->CampaignName;
				$obj['url'] = (string)$campaign->CampaignUrl;
				$obj['status'] = (string)$campaign->ContractStatus;
				$this->_merchant_list[] = $obj;
			}

			$currentPage++;
			$nextPageUri = (string)$res->Campaigns->attributes()->nextpageuri;
			if ($nextPageUri != null) {
				$res = $this->makeCall($nextPageUri);
			}
		}
		return $this->_merchant_list;
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
		$totalTransactions = Array();

		if (!$this->checkConnection()) {
			return $totalTransactions;
		}

		//New Interface
		$url_params = "/Mediapartners/" . $this->_accountSid . "/Actions?ActionDateStart=" . $dStartDate->format('Y-m-d\TH:i:s') . "-00:00&ActionDateEnd=" . $dEndDate->format('Y-m-d\TH:i:s') . "-00:00";
		$res = $this->makeCall($url_params);

		if ($res) {

			$currentPage = (int)$res->Actions->attributes()->page;
			$pageNumber = (int)$res->Actions->attributes()->numpages;
			while ($currentPage <= $pageNumber) {

				foreach ($res->Actions->Action as $action) {

					/*
					 * Example of transaction from API docs
					  "Id": "3641.3299.182972",
					  "CampaignId": 3641,
					  "CampaignName": "Rocket-Powered Products",
					  "ActionTrackerId": 9026,
					  "ActionTrackerName": "irt-17721",
					  "State": "PENDING",
					  "AdId": 339815,
					  "Payout": 32252.1922,
					  "DeltaPayout": 32252.1922,
					  "IntendedPayout": 32252.1922,
					  "Amount": 32252.19,
					  "DeltaAmount": 32252.19,
					  "IntendedAmount": 32252.19,
					  "Currency": "USD",
					  "ReferringDate": "2017-01-11T22:18:42.000Z",
					  "EventDate": "2017-01-12T19:52:07.000Z",
					  "CreationDate": "2017-01-16T05:25:15.000Z",
					  "LockingDate": "2017-02-16T08:00:00.000Z",
					  "ClearedDate": "2017-02-31T08:00:00.000Z",
					  "ReferringType": "CLICK_COOKIE",
					  "ReferringDomain": "www.referring.com",
					  "PromoCode": "SUMMER_PROMO",
					  "Oid": "AZ3456-123V",
					  "CustomerArea": "0",
					  "CustomerCity": "New York",
					  "CustomerRegion": "212",
					  "CustomerCountry": "US",
					  "SubId1": "custom1",
					  "SubId2": "custom2",
					  "SubId3": "custom3",
					  "SharedId": "custom-shared",
					  "Uri": "/Mediapartners/IRBcXt64v4pL159338fdubAiqsbdX65535/Actions/3641.3299.182972.json"
					 */
					$transaction = Array();
					$transaction['unique_id'] = (string)$action->Id;
					$transaction['merchant_id'] = (int)$action->CampaignId;
					$transaction['merchant_name'] = (int)$action->CampaignName;

					$transactionDate = \DateTime::createFromFormat("Y-m-d\TH:i:s", \substr((string)$action->EventDate, 0, 19));
					$transaction['date'] = $transactionDate->format("Y-m-d H:i:s");

					if ((string)$action->ReferringDate != '') {
						// <slawn>
						$transactionDate = \DateTime::createFromFormat("Y-m-d\TH:i:s", \substr((string)$action->ReferringDate, 0, 19));
						$transaction['date_click'] = $transactionDate->format("Y-m-d H:i:s");
					} else {
						$transaction['date_click'] = $transaction['date'];
					}

					if ((string)$action->SharedId != '') {
						$transaction['custom_id'] = (string)$action->SharedId;
					}
					if ((string)$action->SubId1 != '') {
						$transaction['custom_id'] = (string)$action->SubId1;
					}

					$status = (string)$action->State;
					$statusArray[$status] = "";
					if ($status == 'APPROVED' || $status == 'DEFAULT') {
						$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
					} else {
						if ($status == 'REVERSED' || $status == 'REJECTED') {
							$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
						} else {
							$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
						}
					}

					$transaction['currency'] = (string)$action->Currency;
					// Handle either ',' or '.' as decimal separator - 2020-01-29 - PN
					$transaction['amount'] = (double)(str_replace(',','.',$action->Amount));
					$transaction['commission'] = (double)(str_replace(',','.',$action->Payout));
					$transaction['commission_intended'] = (double)(str_replace(',','.',$action->IntendedPayout)); // ??
					$totalTransactions[] = $transaction;
				}

				$currentPage++;
				$nextPageUri = (string)$res->Actions->attributes()->nextpageuri;
				if ($nextPageUri != null) {
					$res = $this->makeCall($nextPageUri);
				}
			}
		}
		return $totalTransactions;

	}

	/**
	 * Get list of deals for all active campaigns
	 * @return array
	 * @throws \Exception
	 */
	public function getDeals()
	{
		$a_deals = Array();
		if (!$this->checkConnection()) {
			return $a_deals;
		}
		$a_merchants = $this->getMerchantList();

		foreach ($a_merchants as $merchant) {
			$merchant_id = $merchant['cid'];
			$url_params = "/Mediapartners/" . $this->_accountSid . "/Campaigns/" . $merchant_id . "/Deals";
			$res = $this->makeCall($url_params);
			$currentPage = (int)$res->Deals->attributes()->page;
			$pageNumber = (int)$res->Deals->attributes()->numpages;
			while ($currentPage <= $pageNumber) {
				foreach ($res->Deals->Deal as $deal) {
					$obj = Array();
					$obj['id'] = (int)$deal->Id;
					$obj['name'] = (string)$deal->Name;
					$obj['description'] = (string)$deal->Description;
					$obj['campaign_id'] = (string)$deal->CampaignId;
					$obj['campaign_name'] = $merchant['name'];
					$obj['status'] = (string)$deal->State;
					$obj['type'] = (string)$deal->Type;
					$obj['discount_type'] = (string)$deal->DiscountType;
					$obj['discount_amount'] = (float)$deal->DiscountAmount;
					$obj['discount_currency'] = (string)$deal->DiscountCurrency;
					$obj['discount_percent'] = (float)$deal->DiscountPercent;
					$obj['discount_max_percent'] = (float)$deal->DiscountMaximumPercent;
					$obj['discount_percent_range_min'] = (float)$deal->DiscountPercentRangeStart;
					$obj['discount_percent_range_max'] = (float)$deal->DiscountPercentRangeEnd;
					$obj['promo_code'] = (string)$deal->DefaultPromoCode;
					$obj['minimum_purchase'] = (float)$deal->MinimumPurchaseAmount;
					$obj['minimum_purchase_currency'] = (string)$deal->MinimumPurchaseCurrency;
					$obj['url'] = ''; // (string)$deal->Uri;  // It' API url, not tracking url

					if (!empty($deal->StartDate)) {
						$start_date = \DateTime::createFromFormat("Y-m-d\TH:i:s", \substr((string)$deal->StartDate, 0, 19));
						$obj['start_date'] = $start_date->format("Y-m-d H:i:s");
					} else {
						$obj['start_date'] = '';
					}

					if (!empty($deal->EndDate)) {
						$end_date = \DateTime::createFromFormat("Y-m-d\TH:i:s", \substr((string)$deal->EndDate, 0, 19));
						$obj['end_date'] = $end_date->format("Y-m-d H:i:s");
					} else {
						$obj['end_date'] = '2099-12-31 00:00:00';
					}

					$a_deals[] = $obj;
				}

				$currentPage++;
				$nextPageUri = (string)$res->Deals->attributes()->nextpageuri;
				if ($nextPageUri != null) {
					$res = $this->makeCall($nextPageUri);
				}
			}
		}
		return $a_deals;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getPaymentHistory()
	{
		$paymentHistory = array();

		$urls = array();
		$urls[] = new \Oara\Curl\Request('https://app.impact.com/secure/nositemesh/accounting/getPayStubParamsCSV.csv', array());
		$exportReport = $this->_client->get($urls);
		$exportData = \str_getcsv($exportReport[0], "\n");

		$num = \count($exportData);
		for ($i = 1; $i < $num; $i++) {
			$paymentExportArray = \str_getcsv($exportData[$i], ",");
			$obj = array();
			$date = \DateTime::createFromFormat("M d, Y", $paymentExportArray[1]);
			$obj['date'] = $date->format("y-m-d H:i:s");
			$obj['pid'] = $paymentExportArray[0];
			$obj['method'] = 'BACS';
			$obj['value'] = \Oara\Utilities::parseDouble($paymentExportArray[6]);
			$paymentHistory[] = $obj;
		}
		return $paymentHistory;
	}

	/**
	 * @param string $params
	 * @return \SimpleXMLElement
	 * @throws \Exception
	 */
	private function makeCall($params = "")
	{
		$base_url = "https://" . $this->_accountSid . ":" . $this->_authToken . "@api.impactradius.com";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $base_url . $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, false);
		$response = curl_exec($ch);
		curl_close($ch);

		if (strpos($response, '<Status>ERROR</Status>') !== false) {
			throw new \Exception($response);
		}

		$xml_encode = utf8_encode($response);

		//Convert the XML string into an SimpleXMLElement object.
		$res = \simplexml_load_string($xml_encode, "SimpleXMLElement", \LIBXML_NOCDATA);

		return $res;
	}

}
