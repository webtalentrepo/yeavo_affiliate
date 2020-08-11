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
 * @category   Ebay
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Ebay extends \Oara\Network
{
	protected $_client = null;
	protected $_sitesAllowed = array();

	/**
	 * @param $credentials
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
		$credentials["password"] = $parameter;

		return $credentials;
	}

	/**
	 * @param string $idSite
	 */
	public function addAllowedSite(string $idSite)
	{
		$this->_sitesAllowed[] = $idSite;
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
	 */
	public function getMerchantList()
	{
		$merchants = array();

		$obj = array();
		$obj['cid'] = "1";
		$obj['name'] = "Ebay";
		$obj['url'] = "https://partnernetwork.ebay.com/";
		$merchants[] = $obj;

		return $merchants;
	}

	/**
	 * @param null $merchantList
	 * @param \DateTime|null $dStartDate
	 * @param \DateTime|null $dEndDate
	 * @return array
	 * @throws \Exception
	 * See: https://partnerhelp.ebay.com/helpcenter/knowledgebase/Transaction-Detail-Report-Changes-and-Reporting-API-Migration-Guidelines/United%20States%20(EN)
	 */
	public function getTransactionList($merchantList = null, \DateTime $dStartDate = null, \DateTime $dEndDate = null)
	{
		$totalTransactions = array();
		//https://<Account_SID>:<Auth_Token>@api.partner.ebay.com/Mediapartners/<Account_SID>/Reports/ebay_partner_transaction_detail.json?STATUS=ALL&START_DATE=<YYYY-MM-DD>&END_DATE=<YYYY-MM-DD>
		$transactions = "https://{$this->_credentials['user']}:{$this->_credentials['password']}@api.partner.ebay.com/Mediapartners/{$this->_credentials['user']}/Reports/ebay_partner_transaction_detail.json";
		$params = array(
			new \Oara\Curl\Parameter('CHECKOUT_SITE', !empty($this->_sitesAllowed) ? implode(',', $this->_sitesAllowed) : '0'), //0 to return all OR Define 1 value from accepted values Accepted Values: US, UK, CA, DE, IT, FR, AU, AT, BE, CH, NL, ES, IE
			new \Oara\Curl\Parameter('STATUS', 'ALL'), //Approved, Pending, OR All
			new \Oara\Curl\Parameter('START_DATE', $dStartDate->format('Y-m-d')), //YYYY-MM-DD
			new \Oara\Curl\Parameter('END_DATE', $dEndDate->format('Y-m-d')), //YYYY-MM-DD
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
		$matches = preg_match('/<title>(.*?)<\/title>/', $curl_results, $matches);
		if (!empty($matches)) {
			throw new \Exception('[Ebay][getTransactionList][Exception] ' . $matches);
		}
		curl_close($ch);
		$transactionsList = json_decode($curl_results, true);

		if (isset($transactionsList['Status']) && $transactionsList['Status'] == 'ERROR' && isset($transactionsList['Message'])) {
			throw new \Exception('[Ebay][getTransactionList][Exception] ' . $transactionsList['Message']);
		}

		foreach ($transactionsList['Records'] as $a_transaction) {
			if (($a_transaction['EventName'] == "Sale" || $a_transaction['EventName'] == "Winning Bid (Revenue)") && (empty($this->_sitesAllowed) || \in_array($a_transaction['CheckoutSite'], $this->_sitesAllowed))) {
				$transaction = Array();
				$transaction['merchantId'] = 0;
				$transaction['merchantName'] = '';
				$transaction['unique_id'] = !empty($a_transaction['EbayCheckoutTransactionId']) ? $a_transaction['EbayCheckoutTransactionId'] : $a_transaction['EpnTransactionId'];
				if (!empty($a_transaction['EpnTransactionId']) && !empty($a_transaction['EbayCheckoutTransactionId']) && ($a_transaction['EbayCheckoutTransactionId'] != $a_transaction['EpnTransactionId'])) {
					echo '[Ebay][getTransactionList][Unique Transaction ID] difference between EbayCheckoutTransactionId and EpnTransactionId ' . $a_transaction['EbayCheckoutTransactionId'];
				}

				$transactionDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", $a_transaction['EventDate'], new \DateTimeZone('America/Denver'));
				$transactionDate->setTimezone(new \DateTimeZone('Europe/Rome'));
				$transaction['date'] = $transactionDate->format("Y-m-d H:i:s");

				$updateDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", $a_transaction['UpdateDate'], new \DateTimeZone('America/Denver'));
				$updateDate->setTimezone(new \DateTimeZone('Europe/Rome'));
				$transaction['update_date'] = $updateDate->format("Y-m-d H:i:s");
				if (isset($a_transaction['CustomId']) && !empty($a_transaction['CustomId'])) {
					$transaction['custom_id'] = $a_transaction['CustomId'];
				}
				$clickDate = \DateTime::createFromFormat("Y-m-d\TH:i:sO", $a_transaction['ClickTimestamp'], new \DateTimeZone('America/Denver'));
				$clickDate->setTimezone(new \DateTimeZone('Europe/Rome'));
				$transaction['click_date'] = $clickDate->format("Y-m-d H:i:s");
				$transaction['amount'] = (float)!empty($a_transaction['DeltaSales']) ? $a_transaction['DeltaSales'] : $a_transaction['Sales'];
				$transaction['commission'] = (float)!empty($a_transaction['DeltaEarnings']) ? $a_transaction['DeltaEarnings'] : $a_transaction['Earnings'];
				if ($a_transaction['Status'] == 'Approved') {
					$transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
				} elseif ($a_transaction['Status'] == 'Pending') {
					$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
				} elseif ($a_transaction['Status'] == 'Reversed') {
					$transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
				} else {
					$transaction['status'] = \Oara\Utilities::STATUS_PENDING;
					echo '[Ebay][getTransactionList] Transaction status unexpected ' . $a_transaction['Status'];
				}

				$totalTransactions[] = $transaction;
			}
		}

		return $totalTransactions;
	}

}
