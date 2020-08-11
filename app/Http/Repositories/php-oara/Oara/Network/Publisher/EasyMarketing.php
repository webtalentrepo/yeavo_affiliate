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
class EasyMarketing extends \Oara\Network
{
	private $_credentials = null;


	/**
	 * @param $credentials
	 * @throws Exception
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
		$credentials[] = $parameter;

		return $credentials;
	}

	/**
	 * @return bool
	 */
	public function checkConnection()
	{
		$connection = true;
		return $connection;
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
		/**
		 * See: https://wiki.easy-m.de/index.php/Publisher:Doku:API
		 * FAQ: https://wiki.easy-m.de/index.php/Publisher:FAQ#Gibt_es_eine_Dokumentation_zur_Transaktions_.2F_Statistik_API
		 */
		$totalTransactions = array();
		try {
			$user = $this->_credentials['user'];
			$password = $this->_credentials['password'];
			$id_site = $this->_credentials['idSite'];
			$a_dates = array();

			if (empty($dStartDate) || empty($dEndDate)) {
				throw new \Exception("[EasyMarketing][getTransactionList][Exception] Date required. Max request time frame is 31 days");
			}
			$interval = $dStartDate->diff($dEndDate);
			$interval_in_days = $interval->days;
			if ($interval_in_days > 30) {
				//Create groups of dates, Get transactions grouping by dates. Max request time frame is 31 days.
				$auxStartDate = clone $dStartDate;
				$auxDate = $auxStartDate->modify("+ 30 days");
				while ($dEndDate > $auxDate) {
					$a_dates[] = array($dStartDate, $auxDate);
					$dStartDate = clone $auxDate;
					$auxStartDate = clone $auxDate;
					$auxDate = $auxStartDate->modify("+ 30 days");
					if ($auxDate >= $dEndDate) {
						$auxDate = $dEndDate;
						$a_dates[] = array($dStartDate, $auxDate);
					}
				}
			} else {
				$a_dates[] = array($dStartDate, $dEndDate);
			}
			foreach ($a_dates as $dates) {
				$dStartDate = $dates[0];
				$dEndDate = $dates[1];
				$url_api_transactions = 'https://' . $user . '/api//' . $password . '/publisher/' . $id_site . '/get-statistic_transactions.xml';
				$params = array(
					new \Oara\Curl\Parameter('condition[period][from]', $dStartDate->format('d.m.Y')),
					new \Oara\Curl\Parameter('condition[period][to]', $dEndDate->format('d.m.Y')),
				);

				$p = array();
				foreach ($params as $parameter) {
					$p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
				}
				$get_params = implode('&', $p);
				$url = $url_api_transactions . '?' . $get_params;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

				$xml = curl_exec($ch);
				if ($xml == 'NO PERMISSION') {
					throw new \Exception("[EasyMarketing][getTransactionList][Exception] Check the access token value. " . $xml);
				} elseif (false == $xml) {
					throw new \Exception("[EasyMarketing][getTransactionList][Exception] Invalid URL: " . $url);
				}
				curl_close($ch);

				//Convert the XML string into an SimpleXMLElement object.
				$xml = @\simplexml_load_string($xml, "SimpleXMLElement", \LIBXML_NOCDATA);
				//Encode the SimpleXMLElement object into a JSON string.
				$json = json_encode($xml);
				$transactionsList = json_decode($json, true);

				if (isset($transactionsList['item'])) {
					if (isset($transactionsList['item']['criterion'])) {
						$a_transaction = self::createTransactionArray($transactionsList['item']);
						$totalTransactions[] = $a_transaction;
					} else {
						foreach ($transactionsList['item'] as $transactionJson) {
							$a_transaction = self::createTransactionArray($transactionJson);
							$totalTransactions[] = $a_transaction;
						}
					}
				} elseif (isset($transactionsList[0]) && strpos($transactionsList[0], "\n") !== false) {
					//no transactions
					continue;
				} else {
					throw new \Exception("[EasyMarketing][getTransactionList][Exception] " . $transactionsList);
				}
			}
		} catch (\Exception $e) {
			throw new \Exception($e);
		}

		return $totalTransactions;

	}

	public function createTransactionArray($transactionJson)
	{

		$transaction = Array();
		$transaction['unique_id'] = $transactionJson['criterion'];
		$transaction['merchantId'] = $transactionJson['campaign_id'];
		$transaction['merchantName'] = $transactionJson['campaign_title'];
		$transaction['date'] = $transactionJson['trackingtime'];
		$transaction['click_date'] = $transactionJson['clicktime'] ?: null;
		$transaction['update_date'] = $transactionJson['processingdate'] ?: null;
		$transaction['paid_date'] = $transactionJson['payoutdate'] ?: null;
		$transaction['amount'] = \Oara\Utilities::parseDouble($transactionJson['turnover']);
		$transaction['commission'] = \Oara\Utilities::parseDouble($transactionJson['provision']);
		//subid
		$transaction['custom_id'] = $transactionJson['subid'] ?: null;
		$transaction['title'] = $transactionJson['trigger_title'];
		$transaction['referrer'] = $transactionJson['referrer'];
		$transaction['action'] = null;

		switch ($transactionJson['status']) {
			case '0':
				$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
				break;
			case '2':
				$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
				break;
			case '1':
			case '3':
				$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
				break;
		}
		switch ($transactionJson['event']) {
			case 'lead':
				$transaction['action'] = \Oara\Utilities::TYPE_LEAD;
				break;
			case 'sale':
				$transaction['action'] = \Oara\Utilities::TYPE_SALE;
				break;
			case 'bonus':
				$transaction['action'] = Utilities::TYPE_BONUS;
				break;
		}
		return $transaction;
	}


}