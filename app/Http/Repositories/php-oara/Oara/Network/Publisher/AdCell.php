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

class AdCell extends \Oara\Network
{
	private $_user_id = null;
	private $_api_password = null;
	private $_token = null;
	/**
	 * API URL
	 * @var string
	 */
	protected $_apiUrl = 'https://www.adcell.de/api/';
	/**
	 * API version
	 * @var string
	 */
	protected $_apiVersion = 'v2';



	/**
	 * @param $credentials
	 * @throws Exception
	 */
	public function login($credentials)
	{
		/**
		 * https://www.adcell.de/api/v2/user/getToken?userName=[userName]&password=[apiPassword]
		 */
		$this->_user_id = $credentials['user'];
		$this->_api_password = $credentials['password'];

		$this->getToken();
	}


	public function getToken() {
		if (!empty($this->_token)) {
			return $this->_token;
		}

		$data = $this->_request(
			'user',
			'getToken',
			array(
				'userName' => $this->_user_id,
				'password' => $this->_api_password,
			)
		);

		if ($data && isset($data['data']['token'])) {
			$this->_token = $data['data']['token']; // Expires in 15 minutes
		}
		return $this->_token;
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
		 * https://www.adcell.de/api/v2/?language=en#&controller=Affiliate_Statistic&apiCall=affiliate_statistic_byCommission
		 */
		$totalTransactions = array();
		try{
			$start_date = $dStartDate->format('Y-m-d');
			$end_date = $dEndDate->format('Y-m-d');
			$page = 1;
			$loop = true;

			while ($loop){

				$transactionsList = $this->_request(
					'affiliate',
					'statistic/byCommission',
					array(
						'token' => $this->getToken(),
						'startDate' => $start_date, //Format YYYY-mm-dd
						'endDate' => $end_date, //Format YYYY-mm-dd
						'page' => $page, //limit 25 rows
					)
				);

				if (isset($transactionsList['data']['items'])){
					if (count($transactionsList['data']['items']) == 0) {
						$loop = false;
						break;
					}
					foreach ($transactionsList['data']['items'] as $transaction) {
						$a_transaction['unique_id'] = $transaction['commissionId'];
						$a_transaction['date'] = $transaction['createTime'];
						$a_transaction['update_date'] = $transaction['changeTime'];
						$a_transaction['change_note'] = $transaction['changeNote'];
						$a_transaction['IP'] = $transaction['ip'];
						$a_transaction['merchantId'] = $transaction['programId'];
						$a_transaction['merchantName'] = $transaction['programName'];
						$a_transaction['campaign_name'] = $transaction['eventName'];
						$a_transaction['custom_id'] = $transaction['subId'];
						$a_transaction['amount'] = Utilities::parseDouble($transaction['totalShoppingCart']);
						$a_transaction['commission'] = Utilities::parseDouble($transaction['totalCommission']);
						$a_transaction['referrer'] = $transaction['referer'];
						switch ($transaction['status']){
							/*
							 * open = open commission
							 * cancelled = cancelled commissions
							 * accepted = approved commissions
							 */
							case 'open':
								$a_transaction['status'] = Utilities::STATUS_PENDING;
								break;
							case 'cancelled':
								$a_transaction['status'] = Utilities::STATUS_DECLINED;
								break;
							case 'accepted':
								$a_transaction['status'] = Utilities::STATUS_CONFIRMED;
								break;
						}
						switch ($transaction['eventType']){
							//Type of event LEAD or SALE
							case 'SALE':
								$a_transaction['event_type'] = Utilities::TYPE_SALE;
								break;
							case 'LEAD':
								$a_transaction['event_type'] = Utilities::TYPE_LEAD;
								break;
						}
						$a_transaction['referrer'] = $transaction['referer'];
						$a_transaction['is_mobile'] = $transaction['isMobile'];
						$totalTransactions[] = $a_transaction;
					}
					$page = (int)(1 + $page);
				}
			}
		}
		catch (\Exception $e){
			throw new \Exception($e);
		}

		return $totalTransactions;

	}


	/**
	 * @return string
	 */
	protected function _getApiBaseUrl() {
		return $this->_apiUrl . $this->_apiVersion;
	}

	/**
	 * @param  string $data
	 * @param  string $format (Optional) Format
	 * @return \stdClass
	 */
	protected function _decode($data, $format = 'json'){
		if ($format == 'json') {
			return json_decode($data, true);
		}
	}

	/**
	 * @param $service
	 * @param $call
	 * @param array $a_options
	 * @return false|\stdClass|string
	 * @throws \Exception
	 */
	protected function _request($service, $call, $a_options) {
		$url = $this->_getApiBaseUrl() . '/' . $service . '/' . $call . '?';

		foreach ($a_options as $key => $value) {
			$url .= '&' . $key . '=' . $value;
		}

		$data = file_get_contents($url);
		if (strlen($data) == 0) {
			throw new \Exception('[AdCell][_request] invalid result received');
		}

		$data = $this->_decode($data);
		if ($data['status'] == 200) {
			return $data;
		} else {
			throw new \Exception($data['message']);
		}
	}




}