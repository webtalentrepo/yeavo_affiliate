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
class Admitad extends \Oara\Network
{
	private $_client_id = null;
	private $_client_secret = null;
	private $_scope = null;
	private $_grant_type = null;
	private $_token = null;

	/**
	 * @param $credentials
	 * @throws Exception
	 */
	public function login($credentials)
	{

		/**
		 * https://github.com/admitad/admitad-php-api
		 * https://developers.admitad.com/en/doc/api_en/auth/auth-client/
		 * Client authorization
		 * To authorize the user, it is required to send POST request to URL https://api.admitad.com/token/,
		 * using data format application/x-www-form/urlencoded and transfer the following parameters
		 * client_id
		 * scope
		 * grant_type
		 *
		 */
		$this->_client_id = $credentials['user'];
		$this->_client_secret = $credentials['password'];
		/**
		 * https://developers.admitad.com/en/doc/api_en/auth/auth-rights/
		 */
		$this->_scope = 'public_data advcampaigns referrals coupons_for_website statistics';
		$this->_grant_type = 'client_credentials';

		$this->getToken();
	}


	public function getToken()
	{
		if (!empty($this->_token)) {
			return $this->_token;
		}
		// Retrieve access token
		$loginUrl = "https://api.admitad.com/token/";

		$params = array(
			new \Oara\Curl\Parameter('grant_type', $this->_grant_type),
			new \Oara\Curl\Parameter('client_id', $this->_client_id),
			new \Oara\Curl\Parameter('scope', $this->_scope)
		);

		$apiKey = base64_encode($this->_client_id . ':' . $this->_client_secret);
		$p = array();
		foreach ($params as $parameter) {
			$p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
		}
		$post_params = implode('&', $p);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $loginUrl);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $apiKey, "Content-Type: application/x-www-form-urlencoded"));

		$curl_results = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($curl_results);
		if ($response && $response->access_token) {
			$this->_token = $response->access_token;
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
	 * @return array
	 * @throws \Exception
	 */
	public function getMerchantList()
	{
		$merchants = Array();
		try {
			//https://developers.admitad.com/en/doc/api_en/methods/advcampaigns/advcampaigns-list/
			$statisticsActions = "https://api.admitad.com/advcampaigns/";
			$limit = 100;
			$offset = 0;
			$loop = true;

			while ($loop) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $statisticsActions . '?limit=' . $limit . '&offset=' . $offset);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->getToken(), "Content-Type: application/x-www-form-urlencoded"));

				$curl_results = curl_exec($ch);
				curl_close($ch);
				$a_merchants = json_decode($curl_results, true);
				foreach ($a_merchants['results'] as $merchantJson) {
					$obj = Array();
					$obj['cid'] = $merchantJson["id"];
					$obj['name'] = $merchantJson["name"];
					$obj['status'] = $merchantJson["status"];
					$obj['url'] = $merchantJson["site_url"];
					$obj['launch_date'] = $merchantJson["activation_date"];
					$merchants[] = $obj;
				}
				if ((int)$a_merchants['_meta']['count'] <= $offset) {
					$loop = false;
				}
				$offset = (int)($limit + $offset);
			}
		} catch (\Exception $e) {
			throw new \Exception('[php-oara][Oara][Network][Publisher][Admitad][getMerchantList][Exception] ' . $e->getMessage());
		}
		return $merchants;
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
		$totalTransactions = array();
		try {
			$limit = 100;
			$offset = 0;
			$loop = true;

			while ($loop) {
				/**
				 * https://developers.admitad.com/en/doc/api_en/methods/statistics/statistics-actions/
				 * The values for dates should be in format %d.%m.%Y
				 * Returns the result in the JSON format
				 */
				$statisticsActions = "https://api.admitad.com/statistics/actions/";
				$params = array(
					new \Oara\Curl\Parameter('date_start', $dStartDate->format("d.m.Y")),
					new \Oara\Curl\Parameter('date_end', $dEndDate->format("d.m.Y")),
					new \Oara\Curl\Parameter('limit', $limit),
					new \Oara\Curl\Parameter('offset', $offset)
				);

				$p = array();
				foreach ($params as $parameter) {
					$p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
				}
				$get_params = implode('&', $p);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $statisticsActions . '?' . $get_params);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->getToken(), "Content-Type: application/x-www-form-urlencoded"));

				$curl_results = curl_exec($ch);
				curl_close($ch);
				$transactionsList = json_decode($curl_results, true);
				foreach ($transactionsList['results'] as $transactionJson) {

					$transaction = Array();
					$transaction['unique_id'] = $transactionJson["action_id"];
					$transaction['merchantId'] = $transactionJson["advcampaign_id"];
					$transaction['merchantName'] = $transactionJson["advcampaign_name"];
					$transaction['date'] = $transactionJson["action_date"];
					$transaction['click_date'] = $transactionJson["click_date"];
					$transaction['update_date'] = $transactionJson["status_updated"];
					$transaction['amount'] = \Oara\Utilities::parseDouble($transactionJson["cart"]);
					$transaction['commission'] = \Oara\Utilities::parseDouble($transactionJson["payment"]);
					$transaction['currency'] = $transactionJson["currency"];
					$transaction['custom_id'] = $transactionJson["subid"];
					$transaction['IP'] = $transactionJson["click_user_ip"];
					$transaction['action'] = $transactionJson["action_type"];
					if ($transactionJson['status'] == 'pending' || strpos($transactionJson['status'], 'hold') !== false || strpos($transactionJson['status'], 'stalled') !== false || strpos($transactionJson['status'], 'delayed') !== false) {
						$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
					} else if ($transactionJson['status'] == 'declined') {
						$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
					} else if ($transactionJson['status'] == 'confirmed' || $transactionJson['status'] == 'approved') {
						$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
					} else {
						$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
						echo '[php-oara][Oara][Network][Publisher][Admitad][getTransactionList] Transaction status unexpected ' . $transactionJson['status'];
					}
					$totalTransactions[] = $transaction;
				}
				if ((int)$transactionsList['_meta']['count'] <= $offset) {
					$loop = false;
				}
				$offset = (int)($limit + $offset);
			}
		} catch (\Exception $e) {
			throw new \Exception('[php-oara][Oara][Network][Publisher][Admitad][getTransactionList][Exception] ' . $e->getMessage());
		}
		return $totalTransactions;
	}

	/**
	 * @param $idSite
	 * @return array
	 * @throws \Exception
	 */
	public function getVouchers($idSite)
	{
		$a_vouchers = Array();
		try {
			//https://developers.admitad.com/en/doc/api_en/methods/coupons/coupons-website/
			$couponsWebsite = "https://api.admitad.com/coupons/website/" . $idSite . '/';

			$limit = 100;
			$offset = 0;
			$loop = true;

			while ($loop) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $couponsWebsite . '?limit=' . $limit . '&offset=' . $offset);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->getToken(), "Content-Type: application/x-www-form-urlencoded"));

				$curl_results = curl_exec($ch);
				curl_close($ch);
				$coupons = json_decode($curl_results, true);
				if (!is_array($coupons) || !isset($coupons['results'])) {
					return $a_vouchers;
				}
				foreach ($coupons["results"] as $couponJson) {
					$obj = Array();
					$obj['promotionId'] = $couponJson["id"];
					$obj['advertiser_id'] = $couponJson["campaign"]["id"];
					$obj['advertiser_name'] = $couponJson["campaign"]["name"];
					if ($couponJson["species"] == 'promocode') {
						$obj['code'] = $couponJson["promocode"];
					} else {
						$obj['code'] = null;
					}
					$obj['is_exclusive'] = $couponJson["exclusive"];
					$obj['name'] = $couponJson["name"];
					$obj['short_description'] = $couponJson["short_name"];
					$obj['description'] = $couponJson["description"];
					$obj['discount_amount'] = abs((int)$couponJson["discount"]);
					$obj['is_percentage'] = (bool)(strpos($couponJson["discount"], '%') !== false);
					$obj['restriction'] = null;
					$obj['start_date'] = $couponJson["date_start"];
					$obj['end_date'] = $couponJson["date_end"];
					$obj['tracking'] = $couponJson["goto_link"];
					// species: Kind of coupon (‘promocode’, ‘action’ - special offer or deal)
					switch ($couponJson["species"]) {
						case 'promocode':
							$obj['type'] = \Oara\Utilities::OFFER_TYPE_VOUCHER;
							break;
						case 'action':
							$obj['type'] = \Oara\Utilities::OFFER_TYPE_DISCOUNT;
							break;
						default:
							$obj['type'] = \Oara\Utilities::OFFER_TYPE_DISCOUNT;
							echo "[php-oara][Oara][Network][Publisher][Admitad][getVouchers] Coupon type unexpected " . $couponJson["type"];
							break;
					}

					$a_vouchers[] = $obj;
				}
				if ((int)$coupons["_meta"]["count"] <= $offset) {
					$loop = false;
				}
				$offset = (int)($limit + $offset);
			}
		} catch (\Exception $e) {
			throw new \Exception('[php-oara][Oara][Network][Publisher][Admitad][getVouchers][Exception] ' . $e->getMessage());
		}

		return $a_vouchers;
	}


}
