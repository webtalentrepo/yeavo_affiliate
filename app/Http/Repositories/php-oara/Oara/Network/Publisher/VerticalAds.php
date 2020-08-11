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
class VerticalAds extends \Oara\Network
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
		 * See: https://data.verticalads.net/account/docs/verticalAds-Zentrale-Publisher-Schnittstelle-v1.3.pdf
		 * https://data.verticalads.net/api/general/v1.3/api.php?user=USERNAME&key=KEY&network=NETWORK
		 * Returns the result in the JSON format (default format)
		 */
		$totalTransactions = array();
		try {
			$user = $this->_credentials['user'];
			$password = $this->_credentials['password'];
			$id_site = $this->_credentials['idSite'];
			$a_dates = array();

			if (empty($dStartDate) || empty($dEndDate)) {
				throw new \Exception("[VerticalAds][getTransactionList][Exception] Date required. Max request time frame is 31 days");
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

				$transactions = "https://data.verticalads.net/api/general/v1.3/api.php";
				$params = array(
					new \Oara\Curl\Parameter('network', $id_site),
					new \Oara\Curl\Parameter('user', $user),
					new \Oara\Curl\Parameter('key', $password),
					new \Oara\Curl\Parameter('startDateConversion', $dStartDate->format('Y-m-d')),
					new \Oara\Curl\Parameter('endDateConversion', $dEndDate->format('Y-m-d')),
				);

				$p = array();
				foreach ($params as $parameter) {
					$p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
				}
				$get_params = implode('&', $p);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $transactions . '?' . $get_params);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

				$curl_results = curl_exec($ch);
				curl_close($ch);
				if (false == $curl_results || strpos($curl_results, 'error') !== false) {
					throw new \Exception('[VerticalAds][getTransactionList][Exception] Invalid response, results: ' . $curl_results);
				}
				$transactionsList = json_decode($curl_results, true);

				if (isset($transactionsList['conversions'])) {
					$n_transactions = count($transactionsList['conversions']);
					if ($n_transactions > 0) {
						if (isset($transactionsList['summary']['totalConversionsCount']) && $n_transactions !== $transactionsList['summary']['totalConversionsCount']) {
							throw new \Exception("[VerticalAds][getTransactionList][Exception] Check the number of transactions. totalConversionsCount: " . $transactionsList['summary']['totalConversionsCount']
								. ' conversions: ' . $n_transactions);
						}
						foreach ($transactionsList['conversions'] as $transactionJson) {

							$a_transaction = self::createTransactionArray($transactionJson);
							$totalTransactions[] = $a_transaction;
						}
					}
				} else {
					throw new \Exception("[VerticalAds][getTransactionList][Exception] " . $transactionsList);
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
		$transaction['unique_id'] = $transactionJson['networkOrderId'];
		$transaction['merchantId'] = $transactionJson['networkCampaignId'];
		$transaction['merchantName'] = $transactionJson['networkCampaignName'];
		$transaction['date'] = $transactionJson['networkDateTimeConversion'];
		$transaction['click_date'] = $transactionJson['networkDateTimeClick'] ?: null;
		$transaction['update_date'] = $transactionJson['networkDateTimeLastUpdated'] ?: null;
		$transaction['amount'] = \Oara\Utilities::parseDouble($transactionJson['turnover']);
		$transaction['commission'] = \Oara\Utilities::parseDouble($transactionJson['commission']);
		$transaction['currency'] = $transactionJson['networkCurrency'];
		//subid
		$transaction['custom_id'] = $transactionJson['networkAdspaceSubid'] ?: null;
		$transaction['title'] = $transactionJson['networkConversionTypeName'];
		$transaction['action'] = null;

		switch ($transactionJson['status']) {
			case 'PENDING':
				$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
				break;
			case 'CANCELED':
				$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
				break;
			case 'APPROVED':
				$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
				break;
		}
		switch ($transactionJson['conversionType']) {
			case 'LEAD':
				$transaction['action'] = \Oara\Utilities::TYPE_LEAD;
				break;
			case 'SALEFIX':
			case 'SALEVAR':
				$transaction['action'] = \Oara\Utilities::TYPE_SALE;
				break;
		}

		if (empty($transactionJson['conversionType']) &&
			empty($transactionJson['networkAdspaceSubid']) &&
			$transactionJson['status'] == 'APPROVED'
		) {
			$transaction['action'] = Utilities::TYPE_BONUS;
		}
		return $transaction;
	}


}