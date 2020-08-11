<?php

namespace Oara\Network\Publisher;

use Oara\Utilities;

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
class ShareASale extends \Oara\Network
{
	/**
	 * API Secret
	 * @var string
	 */
	private $_apiSecret = null;
	/**
	 * API Token
	 * @var string
	 */
	private $_apiToken = null;
	/**
	 * Merchant ID
	 * @var string
	 */
	private $_affiliateId = null;
	/**
	 * Api Version
	 * @var float
	 */
	private $_apiVersion = null;
	/**
	 * Api Server
	 * @var string
	 */
	private $_apiServer = null;

	/**
	 * Constructor and Login
	 * @param $credentials
	 * @return ShareASale
	 */
	public function login($credentials)
	{
		$this->_affiliateId = preg_replace("/[^0-9]/", "", $credentials['affiliateId']);
		$this->_apiToken = $credentials['apiToken'];
		$this->_apiSecret = $credentials['apiSecret'];
		$this->_apiVersion = 2.3;
		$this->_apiServer = "https://shareasale.com/x.cfm?";
	}

	/**
	 * @return array
	 */
	public function getNeededCredentials()
	{
		$credentials = array();

		$parameter = array();
		$parameter["description"] = "Affiliate ID";
		$parameter["required"] = true;
		$parameter["name"] = "Affiliate ID";
		$credentials["affiliateid"] = $parameter;

		$parameter = array();
		$parameter["description"] = "API token";
		$parameter["required"] = true;
		$parameter["name"] = "API token";
		$credentials["apitoken"] = $parameter;

		$parameter = array();
		$parameter["description"] = "API secret";
		$parameter["required"] = true;
		$parameter["name"] = "API secret";
		$credentials["apisecret"] = $parameter;

		return $credentials;
	}

	/**
	 * @return bool
	 */
	public function checkConnection()
	{
		$connection = true;

		$returnResult = self::makeCall("apitokencount");
		if ($returnResult) {
			//parse HTTP Body to determine result of request
			if (stripos($returnResult, "Error Code ")) { // error occurred
                echo "[ShareASale][checkConnection][Error 1] " . $returnResult . PHP_EOL;
				$connection = false;
			}
			else {
                echo "[ShareASale][checkConnection][Remaining Calls] " . $returnResult . PHP_EOL;
            }
		} else { // connection error
            echo "[ShareASale][checkConnection][Error 2] " . $returnResult . PHP_EOL;
			$connection = false;
		}
		return $connection;
	}

	/**
	 * @return array
	 */
	public function getMerchantList()
	{

		$merchants = array();

		$returnResult = self::makeCall("merchantStatus");
		$exportData = str_getcsv($returnResult, "\r\n");
		$num = count($exportData);
		for ($i = 1; $i < $num; $i++) {
			$merchantArray = str_getcsv($exportData[$i], "|");
			if (count($merchantArray) > 1) {
				$obj = Array();
				$obj['cid'] = (int)$merchantArray[0];
				$obj['name'] = $merchantArray[1];
				$obj['url'] = $merchantArray[2];
				//Partnership Status: Declined, Yes, Pending
				$obj['status'] = $merchantArray[8];
				$merchants[] = $obj;
			}
		}

		return $merchants;
	}

	/**
	 * See: https://account.shareasale.com/a-apimanager.cfm?
	 * @return array
	 * @throws \Exception
	 */
	public function getVouchers()
	{
		$totalDeals = array();
		//Add current=1 to view only current deals. Default is 0
		$returnResult = self::makeCall("couponDeals", "&current=1");
		if (stripos($returnResult, "Error Code ")) {
			// error occurred
			echo "[ShareASale][Error] " . $returnResult . PHP_EOL;
			var_dump($returnResult);
			throw new \Exception($returnResult);
		}
		$exportData = str_getcsv($returnResult, "\r\n");
		$num = count($exportData);
		for ($i = 1; $i < $num; $i++) {
			$dealExportArray = str_getcsv($exportData[$i], "|");
			if (count($dealExportArray) < 17) {
				continue;
			}
			$deal = Array();
			$deal['promotionId'] = (int)$dealExportArray[0];
			$deal['advertiser_id'] = (int)$dealExportArray[1];
			$deal['advertiser_name'] = (int)$dealExportArray[2];
			if (isset($dealExportArray[3])) {
				//ShareASale.com Inc. Chicago IL 60654
				$deal["start_date"] = new \DateTime($dealExportArray[3], new \DateTimeZone('America/Chicago'));
				$deal["start_date"]->setTimezone(new \DateTimeZone('Europe/Rome'));
			}
			if (isset($dealExportArray[4])) {
				//ShareASale.com Inc. Chicago IL 60654
				$deal["end_date"] = new \DateTime($dealExportArray[4], new \DateTimeZone('America/Chicago'));
				$deal["end_date"]->setTimezone(new \DateTimeZone('Europe/Rome'));
			}
			$deal['name'] = $dealExportArray[6];
			$deal['tracking'] = $dealExportArray[8];
			$deal['description'] = $dealExportArray[11];
			$deal['restriction'] = $dealExportArray[12];
			$deal['code'] = $dealExportArray[14];
			if (!empty($deal['code'])) {
				$deal['type'] = Utilities::OFFER_TYPE_VOUCHER;
			} else {
				$deal['type'] = Utilities::OFFER_TYPE_DISCOUNT;
			}
			$deal['update_date'] = $dealExportArray[15];
			$totalDeals[] = $deal;
		}
		return $totalDeals;
	}


	/**
	 * See: https://account.shareasale.com/a-apimanager.cfm?
	 * @param null $merchantList
	 * @param \DateTime|null $dStartDate
	 * @param \DateTime|null $dEndDate
	 * @return array
	 * @throws \Exception
	 */
	public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
	{
		$totalTransactions = array();

		$returnResult = self::makeCall("activity", "&dateStart=" . $dStartDate->format("m/d/Y") . "&dateEnd=" . $dEndDate->format("m/d/Y"));
		if (stripos($returnResult, "Error Code ")) {
			// error occurred
			echo "[ShareASale][Error] " . $returnResult . PHP_EOL;
			var_dump($returnResult);
			throw new \Exception($returnResult);
		}
		$exportData = str_getcsv($returnResult, "\r\n");
		$num = count($exportData);
		for ($i = 1; $i < $num; $i++) {
			$transactionExportArray = str_getcsv($exportData[$i], "|");
			if (count($transactionExportArray) < 26) {
				continue;
			}
			$transaction = Array();
			$transaction['unique_id'] = (int)$transactionExportArray[0];
			if (isset($transactionExportArray[1])) {
				$transaction["affiliate_ID"] = (int)$transactionExportArray[1];
			}
			$transaction['merchantId'] = (int)$transactionExportArray[2];
			if (isset($transactionExportArray[20])) {
				$transaction['campaign_name'] = $transactionExportArray[20];
			}
			if (isset($transactionExportArray[3])) {
				//ShareASale.com Inc. Chicago IL 60654
				$transaction["date"] = new \DateTime($transactionExportArray[3], new \DateTimeZone('America/Chicago'));
				$transaction["date"]->setTimezone(new \DateTimeZone('Europe/Rome'));
			}
			$transaction['amount'] = Utilities::parseDouble($transactionExportArray[4]);
			$transaction['commission'] = Utilities::parseDouble($transactionExportArray[5]);
			$transaction['currency'] = 'USD';
			$comment = null;
			if (isset($transactionExportArray[6])) {
				$comment = $transactionExportArray[6];
				$transaction['comment'] = $comment;
			}
			$voided = null;
			if (isset($transactionExportArray[7])) {
				$voided = $transactionExportArray[7];
				$transaction["voided"] = $voided;
			}
			$pending_date = null;
			if (isset($transactionExportArray[8])) {
				$pending_date = $transactionExportArray[8];
				$transaction["pending_date"] = $pending_date;
			}
			$locked = null;
			if (isset($transactionExportArray[9])) {
				$locked = $transactionExportArray[9];
				$transaction["locked"] = $locked;
			}
			if (isset($transactionExportArray[10])) {
				$transaction['custom_id'] = $transactionExportArray[10];
			}
			if (isset($transactionExportArray[11])) {
				$transaction['referrer'] = $transactionExportArray[11];
			}
			$reversal_date = null;
			if (isset($transactionExportArray[12])) {
				$reversal_date = $transactionExportArray[12];
				$transaction["reversal_date"] = $reversal_date;
			}
			if (isset($transactionExportArray[13]) && isset($transactionExportArray[14])) {
				$str_to_time_date = strtotime($transactionExportArray[13]);
				$date = date("Y-m-d", $str_to_time_date);
				$str_to_time_time = strtotime($transactionExportArray[14]);
				$time = date("H:i:s", $str_to_time_time);
				$click_date = $date . " " . $time;

				$transaction["click_date"] = new \DateTime($click_date, new \DateTimeZone('America/Chicago'));
				$transaction["click_date"]->setTimezone(new \DateTimeZone('Europe/Rome'));
			}
			if (isset($transactionExportArray[15])) {
				$banner_id = $transactionExportArray[15];
				$transaction["banner_id"] = $banner_id;
			}
			if (isset($transactionExportArray[16])) {
				$sku_list = $transactionExportArray[16];
				$transaction["sku_list"] = $sku_list;
			}
			if (isset($transactionExportArray[17])) {
				$quantity_list = $transactionExportArray[17];
				$transaction["quantity_list"] = $quantity_list;
			}
			$lock_date = null;
			if (isset($transactionExportArray[18])) {
				$lock_date = $transactionExportArray[18];
				$transaction["lock_date"] = $lock_date;
			}
			$paid_date = null;
			if (isset($transactionExportArray[19])) {
				$paid_date = $transactionExportArray[19];
				$transaction["paid_date"] = $paid_date;
			}
			if (!empty($voided)) {
				$transaction['status'] = Utilities::STATUS_DECLINED;
			} elseif (empty($locked) && !empty($lock_date)) {
				$transaction['status'] = Utilities::STATUS_PENDING;
			} elseif (!empty($locked) && 1 == $locked) {
				//Locked transactions are transactions that are eligible for payment.
				$transaction['status'] = Utilities::STATUS_CONFIRMED;
			}

			if (isset($transactionExportArray[22]) && $transactionExportArray[22] == 'Sale') {
				$transaction['trans_type'] = Utilities::TYPE_SALE;
			} elseif (isset($transactionExportArray[22]) && $transactionExportArray[22] == 'Lead') {
				$transaction['trans_type'] = Utilities::TYPE_LEAD;
			}
			$totalTransactions[] = $transaction;
		}
		return $totalTransactions;
	}

	/**
	 * @param $actionVerb
	 * @param string $params
	 * @return mixed
	 */
	private function makeCall($actionVerb, $params = "")
	{

		$myTimeStamp = gmdate(DATE_RFC1123);
		$sig = $this->_apiToken . ':' . $myTimeStamp . ':' . $actionVerb . ':' . $this->_apiSecret;

		$sigHash = hash("sha256", $sig);
		$myHeaders = array("x-ShareASale-Date: $myTimeStamp", "x-ShareASale-Authentication: $sigHash");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_apiServer . "affiliateId=" . $this->_affiliateId . "&token=" . $this->_apiToken . "&version=" . $this->_apiVersion . "&action=" . $actionVerb . $params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $myHeaders);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$returnResult = curl_exec($ch);
		curl_close($ch);
		return $returnResult;
	}
}
