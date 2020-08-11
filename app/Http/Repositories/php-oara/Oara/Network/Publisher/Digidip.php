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

class Digidip extends \Oara\Network
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
		 * https://digidip.net/api/documentation
		 * Retrieve a list of transactions (contains extended data). Max request time frame is 7 days
		 * The values for dates should be in in unix timestamp
		 * Returns the result in the JSON format
		 */
		$totalTransactions = array();
		try{
			$user = $this->_credentials['user'];
			$password = $this->_credentials['password'];
			$id_site = $this->_credentials['idSite'];
			$loop = true;
			$a_dates = array();

			if (empty($dStartDate) || empty($dEndDate)){
				throw new \Exception("[Digidip][getTransactionList][Exception] Date required. Max request time frame is 7 days");
			}
			$interval = $dStartDate->diff($dEndDate);
			$interval_in_days = $interval->days;
			if ($interval_in_days > 6){
				//Create groups of dates, Get transactions grouping by dates. Max request time frame is 7 days.
				$auxStartDate = clone $dStartDate;
				$auxDate = $auxStartDate->modify("+ 6 days");
				while ($dEndDate > $auxDate){
					$a_dates[] = array($dStartDate, $auxDate);
					$dStartDate = clone $auxDate;
					$auxStartDate = clone $auxDate;
					$auxDate = $auxStartDate->modify("+ 6 days");
					if ($auxDate >= $dEndDate){
						$auxDate = $dEndDate;
						$a_dates[] = array($dStartDate, $auxDate);
					}
				}
			}
			else{
				$a_dates[] = array($dStartDate, $dEndDate);
			}
			foreach ($a_dates as $dates){
				$dStartDate = $dates[0];
				$dEndDate = $dates[1];
				$page = 0;
				while ($loop){

					$transactions = "https://api.digidip.net/detailed-transactions";
					$params = array(
						new \Oara\Curl\Parameter('project_id',  $id_site),
						new \Oara\Curl\Parameter('timestamp_start',  $dStartDate->getTimestamp()),
						new \Oara\Curl\Parameter('timestamp_end', $dEndDate->getTimestamp()),
						new \Oara\Curl\Parameter('page', $page),
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
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . \base64_encode($user . ':' . $password), "Accept: application/json"));

					$curl_results = curl_exec($ch);
					curl_close($ch);
					$transactionsList = json_decode($curl_results, true);

					if (isset($transactionsList['data'])){
						if (count($transactionsList['data']) == 0) {
							break;
						}
						foreach ($transactionsList['data'] as $transactionJson) {

							$a_transaction = self::createTransactionArray($transactionJson);
							$totalTransactions[] = $a_transaction;
						}
						$page = (int)(1 + $page);
					}
					elseif (empty($transactionsList)){
						throw new \Exception("[Digidip][getTransactionList][Exception] Check authorization params");
					}
					else{
						if (isset($transactionsList['message'])){
							throw new \Exception("[Digidip][getTransactionList][Exception] " . $transactionsList['message']);
						}
						$loop = false;
					}
				}
			}
		}
		catch (\Exception $e){
			throw new \Exception($e);
		}

		return $totalTransactions;

	}

	/**
	 * @param null $merchantList
	 * @param \DateTime|null $dStartDate
	 * @param \DateTime|null $dEndDate
	 * @return array
	 */
	public function getTransactions($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
	{
		$user = $this->_credentials['user'];
		$password = $this->_credentials['password'];
		$id_site = $this->_credentials['idSite'];
		$totalTransactions = array();
		$limit = 1000;
		$page = 0;
		$loop = true;

		while ($loop){
			/**
			 * https://digidip.net/api/documentation
			 * The values for dates should be in in unix timestamp
			 * Returns the result in the JSON format
			 */
			$transactions = "https://api.digidip.net/transactions";
			$params = array(
				new \Oara\Curl\Parameter('project_id',  $id_site),
				new \Oara\Curl\Parameter('timestamp_start',  $dStartDate->timestamp),
				new \Oara\Curl\Parameter('timestamp_end', $dEndDate->timestamp),
				new \Oara\Curl\Parameter('page', $page),
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
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . \base64_encode($user . ':' . $password), "Accept: application/json"));

			$curl_results = curl_exec($ch);
			curl_close($ch);
			$transactionsList = json_decode($curl_results, true);

			foreach ($transactionsList['data'] as $transactionJson) {

				$transaction_id = $transactionJson["transaction_id"];
				$transaction_href = $transactionJson["_links"][0]["href"];

				$transaction = self::getTransaction($transaction_id);

				$totalTransactions[] = $transaction;
			}
			if (count($transactionsList["data"]) <= $limit) {
				$loop = false;
			}
			$page = (int)(1 + $page);
		}
		return $totalTransactions;

	}

	public function getTransaction($transaction_id){

		$user = $this->_credentials['user'];
		$password = $this->_credentials['password'];

		$transaction = "https://api.digidip.net/transactions/" . $transaction_id;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $transaction);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . \base64_encode($user . ':' . $password), "Accept: application/json"));

		$curl_results = curl_exec($ch);
		curl_close($ch);
		$transactionsList = json_decode($curl_results, true);

		foreach ($transactionsList['data'] as $transactionJson) {

			return self::createTransactionArray($transactionJson);

		}
	}

	public function createTransactionArray($transactionJson){

		$transaction = Array();
		$transaction['unique_id'] = $transactionJson['id'];
		$transaction['merchantId'] = $transactionJson['merchant']['id'];
		$transaction['merchantName'] = $transactionJson['merchant']['name'];
		$transaction['date'] = $transactionJson['date']['iso8601'];
		$transaction['click_date'] = $transactionJson['click']['date']['iso8601'] ?? null;
		$transaction['update_date'] = $transactionJson['last_modified_date']['iso8601'] ?? null;
		$transaction['amount'] = \Oara\Utilities::parseDouble($transactionJson['price']['amount']);
		$transaction['commission'] = \Oara\Utilities::parseDouble($transactionJson['commission']['amount']);
		$transaction['currency'] = $transactionJson['commission']['currency'];
		$transaction['custom_id'] = $transactionJson['click']['custom_subid']['content'] ?? null;
		$transaction['IP'] = null;
		$transaction['action'] = null;

		switch ($transactionJson["status"]){
			case 'pending':
			case 'added':
			case 'received':
				$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
				break;
			case 'denied':
				$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
				break;
			case 'validated':
			case 'payment processing':
			case 'paid':
				$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
				break;
		}
		return $transaction;
	}





}