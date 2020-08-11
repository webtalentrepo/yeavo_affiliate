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
 * @category   Ls
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class LinkShare extends \Oara\Network
{

    public $_nid = null;
    protected $_sitesAllowed = array();
    private $_client = null;
    private $_siteList = array();
    private $_idSite = null;
    private $_token = null;
    private $_user = null;
    private $_password = null;
    private $_bearer = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {
        $this->_user = $credentials ['user'];
        $this->_password = $credentials ['password'];
        $this->_idSite = $credentials ['idSite'];

        // If the Bearer authentication token is defined into environment use it directly to get the access token - <PN> 2019-12-10
        if (isset($_ENV['LINKSHARE_TOKEN'])) {
            $this->_bearer = $_ENV['LINKSHARE_TOKEN'];
            if (!empty($this->_bearer)) {
                $this->getToken($this->_bearer);
                // Create a dummy site structure (allows access to only one site at a time)
                $site = new \stdClass ();
                $site->website = '';
                $site->url = '';;
                $site->id = $this->_idSite;
                $site->token = $this->_token;
                if (isset($_ENV['LINKSHARE_SECURITY_TOKEN'])) {
                    // Get security token from the environment - 2019-10-12 <PN>
                    $site->secureToken = $_ENV['LINKSHARE_SECURITY_TOKEN'];
                }
                else {
                    $site->secureToken = '';;
                }
                $siteList [] = $site;
                $this->_siteList = $siteList;
            }
        }
        else {
            // Try to login as a dashboard user to grab token from web services page
            $this->_client = new \Oara\Curl\Access ($credentials);

            $loginUrl = 'https://login.linkshare.com/sso/login?service=' . \urlencode("http://cli.linksynergy.com/cli/publisher/home.php");
            $valuesLogin = array(
                new \Oara\Curl\Parameter ('HEALTHCHECK', 'HEALTHCHECK PASSED.'),
                new \Oara\Curl\Parameter ('username', $this->_user),
                new \Oara\Curl\Parameter ('password', $this->_password),
                new \Oara\Curl\Parameter ('login', 'Log In')
            );

            $urls = array();
            $urls [] = new \Oara\Curl\Request ($loginUrl, array());
            $exportReport = $this->_client->get($urls);
            $doc = new \DOMDocument();
            @$doc->loadHTML($exportReport[0]);
            $xpath = new \DOMXPath($doc);
            $hidden = $xpath->query('//input[@type="hidden"]');
            foreach ($hidden as $values) {
                $valuesLogin[] = new \Oara\Curl\Parameter($values->getAttribute("name"), $values->getAttribute("value"));
            }
            $doc = new \DOMDocument();
            @$doc->loadHTML($exportReport[0]);
            $xpath = new \DOMXPath($doc);
            $formList = $xpath->query('//form');
            foreach ($formList as $form) {
                $loginUrl = "https://login.linkshare.com" . $form->getAttribute("action");
            }
            $urls = array();
            $urls [] = new \Oara\Curl\Request ($loginUrl, $valuesLogin);
            $this->_client->post($urls);
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

    public function getToken($apiKey) {
        if (!empty($this->_token)) {
            return $this->_token;
        }

        if (empty($apiKey)) {
            if (!empty($this->_bearer)) {
                $apiKey = $this->_bearer;
            }
            else {
                // If the Bearer authentication token is defined into environment use it directly to get the access token - <PN> 2019-12-10
                if (isset($_ENV['LINKSHARE_TOKEN'])) {
                    $this->_bearer = $_ENV['LINKSHARE_TOKEN'];
                    if (!empty($this->_bearer)) {
                        $apiKey = $this->_bearer;
                    }
                }
            }
        }

        // Retrieve access token
        $loginUrl = "https://api.rakutenmarketing.com/token";

        $params = array(
            new \Oara\Curl\Parameter('grant_type', 'password'),
            new \Oara\Curl\Parameter('username', $this->_user),
            new \Oara\Curl\Parameter('password', $this->_password),
            new \Oara\Curl\Parameter('scope', $this->_idSite)
        );

        $p = array();
        foreach ($params as $parameter) {
            $p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
        }
        $post_params = implode('&', $p);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $apiKey));

        $curl_results = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($curl_results);
        if ($response && $response->access_token) {
            $this->_token = $response->access_token;
        }
        return $this->_token;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        if ($this->_siteList) {
            // Already have a valid connection!
            return true;
        }

        // OLD connection trying to simulate a user login to scrape the report API token
        // Replaced on 2019-12-10 with raw LINKSHARE_SECURITY_TOKEN set into environment and encrypted in getTransactionList() function
        // ... It's not supposed to enter here anymore if login() succeeded
        $connection = false;

        $urls = array();

        $urls [] = new \Oara\Curl\Request ('http://cli.linksynergy.com/cli/publisher/home.php?', array());
        $result = $this->_client->get($urls);

        // Check if the credentials are right
        if (\preg_match('/https:\/\/cli\.linksynergy\.com\/cli\/common\/logout\.php/', $result [0], $matches)) {

            $urls = array();
            $urls [] = new \Oara\Curl\Request ('https://cli.linksynergy.com/cli/publisher/my_account/marketingChannels.php', array());
            $exportReport = $this->_client->get($urls);

            $doc = new \DOMDocument();
            @$doc->loadHTML($exportReport[0]);
            $xpath = new \DOMXPath($doc);
            $results = $xpath->query('//table');
            foreach ($results as $table) {
                $tableCsv = \Oara\Utilities::htmlToCsv(\Oara\Utilities::DOMinnerHTML($table));
            }

            $resultsSites = array();
            $num = \count($tableCsv);
            for ($i = 1; $i < $num; $i++) {
                $siteArray = \str_getcsv($tableCsv [$i], ";");
                if (isset ($siteArray [2]) && \is_numeric($siteArray [2])) {
                    $result = array();
                    $result ["id"] = $siteArray [2];
                    $result ["name"] = $siteArray [1];
                    $result ["url"] = "https://cli.linksynergy.com/cli/publisher/common/changeCurrentChannel.php?sid=" . $result ["id"];
                    $resultsSites [] = $result;
                }
            }

            $siteList = array();
            foreach ($resultsSites as $resultSite) {
                $site = new \stdClass ();
                $site->website = $resultSite ["name"];
                $site->url = $resultSite ["url"];
                $parsedUrl = \parse_url($site->url);
                $attributesArray = \explode('&', $parsedUrl ['query']);
                $attributeMap = array();
                foreach ($attributesArray as $attribute) {
                    $attributeValue = \explode('=', $attribute);
                    $attributeMap [$attributeValue [0]] = $attributeValue [1];
                }
                $site->id = $attributeMap ['sid'];
                // Login into the Site ID
                $urls = array();
                $urls [] = new \Oara\Curl\Request ($site->url, array());
                $this->_client->get($urls);

                $urls = array();
                $urls [] = new \Oara\Curl\Request ('https://cli.linksynergy.com/cli/publisher/reports/reporting.php', array());
                $result = $this->_client->get($urls);
                if (preg_match_all('/\"token_one\"\: \"(.+)\"/', $result[0], $match)) {
                    $site->token = $match[1][0];
                }

                $urls = array();
                $urls [] = new \Oara\Curl\Request ('http://cli.linksynergy.com/cli/publisher/links/webServices.php', array());
                $result = $this->_client->get($urls);
                if (preg_match_all('/<div class="token">(.+)<\/div>/', $result[0], $match)) {
                    $site->secureToken = $match[1][1];
                }

                $siteList [] = $site;

            }
            $connection = true;
            $this->_siteList = $siteList;
        }
        return $connection;
    }



    public function getMerchantList()
    {
        $arrResult = array();
        try {
            if (empty($this->_token)) {
                $this->_token = $this->getToken('');
            }

            // https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/{status}

            // Get all merchants with status "approved"

            $url = "https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/approved";
            $arrResult = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->_token));

            $curl_results = curl_exec($ch);
            curl_close($ch);

            $response = xml2array($curl_results);
            if (!is_array($response) || count($response) <= 0) {
                $message = 'Linkshare: getMerchantList XML Error';
                throw new \Exception($message);
            }
            if (!isset($response['ns1:getMerchByAppStatusResponse'])) {
                $message = 'Linkshare: getMerchantList XML Error';
                throw new \Exception($message);
            }
            $result = $response['ns1:getMerchByAppStatusResponse'];
            $merchants = $result['ns1:return'];
            foreach ($merchants as $key => $merchant) {
                $mid = isset($merchant['ns1:mid']) ? $merchant['ns1:mid'] : '';
                $name = isset($merchant['ns1:name']) ? $merchant['ns1:name'] : '';
                $status = isset($merchant['ns1:applicationStatus']) ? $merchant['ns1:applicationStatus'] : '';
                $arrResult[] = array(
                    'cid' => $mid,
                    'name' => $name,
                    'status' => $status,
                    'termination_date' => null,
                    'url' => null,
                );
            }
            return $arrResult;
        }
        catch (\Exception $e) {
            echo "LinkShare getMerchantList error: ".$e->getMessage()."\n ";
            throw new \Exception($e);
        }
        return $arrResult;
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
        $merchantIdList = \Oara\Utilities::getMerchantIdMapFromMerchantList($merchantList);

        foreach ($this->_siteList as $site) {
            if (!empty($this->_idSite) && !$site == $this->_idSite){
                break;
            }
            if (empty($this->_sitesAllowed) || in_array($site->id, $this->_sitesAllowed)) {
                echo "LinkShare - Get Transactions for site " . $site->id . PHP_EOL;

                $security_token = $site->secureToken;
                if (empty($security_token)) {
                    echo "LinkShare ERROR - Empty Security Token for site " . $site->id . PHP_EOL;
                    return $totalTransactions;
                }
                // Encode security token in standard Linkshare serialization structure - 2019-12-10
                $encrypted = 'encrypteda:2:{s:5:"Token";s:{len}:"{token}";s:8:"UserType";s:9:"Publisher";}';
                $encrypted = str_replace("{token}", $security_token, $encrypted);
                $encrypted = str_replace("{len}", strlen($security_token), $encrypted);
                $token64 = urlencode(base64_encode($encrypted));

                // WARNING: You must create a custom report called exactly "Individual Item Report + Transaction ID + Currency"
                // adding to the standard item report the columns "Transaction ID" and "Currency"
                $url = "https://ran-reporting.rakutenmarketing.com/en/reports/Individual-Item-Report-%2B-Transaction-ID-%2B-Currency/filters?start_date=" . $dStartDate->format("Y-m-d") . "&end_date=" . $dEndDate->format("Y-m-d") . "&include_summary=N" . "&network=" . $this->_nid . "&tz=GMT&date_type=transaction&token=" . $token64;
                $result = $this->getRemoteUrl($url);
                // $result = file_get_contents($url);

                // Signature Orders Report is a standard report already defined on the dashboard reports section
                $url = "https://ran-reporting.rakutenmarketing.com/en/reports/signature-orders-report/filters?start_date=" . $dStartDate->format("Y-m-d") . "&end_date=" . $dEndDate->format("Y-m-d") . "&include_summary=N" . "&network=" . $this->_nid . "&tz=GMT&date_type=transaction&token=" . $token64;
                $resultSignature = $this->getRemoteUrl($url);
                // $resultSignature = file_get_contents($url);

                $signatureMap = array();
                $exportData = str_getcsv($resultSignature, "\n");
                $num = count($exportData);
                for ($j = 1; $j < $num; $j++) {
                    $signatureData = str_getcsv($exportData [$j], ",");
                    $orderId = $signatureData[3];
                    // BV-886 - Special case ... comma in order id ... remove it
                    if (strpos($orderId,",") !== false) {
                        $orderId = str_replace(",","",$orderId);
                    }
                    $signatureMap[$orderId] = $signatureData[0];
                }

                $exportData = \str_getcsv($result, "\n");
                $num = \count($exportData);
                for ($j = 1; $j < $num; $j++) {
                    try {
                        $transactionData = \str_getcsv($exportData [$j], ",");

                        if (count($transactionData) > 10 && (count($merchantIdList)==0 || isset($merchantIdList[$transactionData[3]]))) {
                            if ($transactionData[1] === '' && strpos($transactionData[2],'/') !== false) {
                                // BV-886 - Special case ... empty field after transaction id ... remove from array
                                unset($transactionData[1]);
                                $transactionData = array_values($transactionData);
                            }
                            $transaction = array();
                            $transaction['merchantId'] = ( int )$transactionData[3];
                            $transaction['merchantName'] = $transactionData[4];
                            $transactionDate = \DateTime::createFromFormat("m/d/y H:i:s", $transactionData[1] . " " . $transactionData[2]);

                            // $transaction['date'] = $transactionDate->format("Y-m-d H:i:s");
                            $transaction['date'] = $transactionDate->format("Y-m-d H:i:s") . '+00:00';

                            if (isset($signatureMap[$transactionData[0]])) {
                                $transaction['custom_id'] = $signatureMap[$transactionData[0]];
                            }
                            else {
                                echo "[LinkShare][getTransactionsList] Warning: Cannot find signature (u1) for order id " . $transactionData[0] . " transaction id " . $transactionData[10];
                            }
                            $transaction['unique_id'] = $transactionData[10];
                            $transaction['currency'] = $transactionData[11];

                            // $sales = $filter->filter($transactionData[7]);
                            $sales = \Oara\Utilities::parseDouble($transactionData[7]);

                            if ($sales != 0) {
                                $transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                            } else if ($sales == 0) {
                                $transaction['status'] = \Oara\Utilities::STATUS_PENDING;
                            }

                            $transaction['amount'] = \Oara\Utilities::parseDouble($transactionData[7]);

                            $transaction['commission'] = \Oara\Utilities::parseDouble($transactionData[9]);

                            if ($transaction['commission'] < 0) {
                                $transaction['amount'] = abs($transaction['amount']);
                                $transaction['commission'] = abs($transaction['commission']);
                                $transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
                            }
                            $transaction['IP'] = '';    // not available
                            $totalTransactions [] = $transaction;
                        }
                    }
                    catch (\Exception $e) {
                        echo "[LinkShare][getTransactionsList] Error: " . $e->getMessage();
                    }
                }
            }
        }
        return $totalTransactions;
    }

    /**
     * Get list of Vouchers / Coupons / Offers
     * @param $apiKey   Api Key is needed to access data feed
     * @return array
     */
    public function getVouchers($apiKey, $network)
    {
        $vouchers = array();

        try {
            if (empty($this->_token)) {
                $this->_token = $this->getToken($apiKey);
            }

            // https://api.rakutenmarketing.com/coupon/1.0?category=16&promotiontype=31&network=1&resultsperpage=100&pagenumber=2


            $loginUrl = "https://api.rakutenmarketing.com/coupon/1.0";
            $currentPage = 1;
            $arrResult = array();
            if (strpos($network,',') !== false) {
                // If more than one networks are provided ... don't use network parameter to get ALL networks - 2019-06-24 <PN>
                $network = null;
            }
            while (true) {
                $params = array(
                    // Optional parameters category / promotiontype
                    // new \Oara\Curl\Parameter('category', '1|2|3|4|5|6|7|8'),
                    // new \Oara\Curl\Parameter('promotiontype', 31),
                    // new \Oara\Curl\Parameter('network', $network),
                    new \Oara\Curl\Parameter('resultsperpage', 100),
                    new \Oara\Curl\Parameter('pagenumber', $currentPage)
                );
                if (!empty($network) && $network == intval($network)) {
                    // Add network parameter only if a unique valid integer value
                    $params[] = new \Oara\Curl\Parameter('network', $network);
                }

                $p = array();
                foreach ($params as $parameter) {
                    $p[] = $parameter->getKey() . '=' . \urlencode($parameter->getValue());
                }
                $post_params = implode('&', $p);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $loginUrl . '?' . $post_params);
                curl_setopt($ch, CURLOPT_POST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->_token));

                $curl_results = curl_exec($ch);
                curl_close($ch);

                $response = xml2array($curl_results);
                if (!is_array($response) || count($response) <= 0) {
                    return $arrResult;
                }
                if (!isset($response['couponfeed'])) {
                    if (isset($response['ams:fault'])) {
                        $message = 'Linkshare: ' . $response['ams:fault']['ams:message'] . ' - ' . $response['ams:fault']['ams:description'];
                        throw new \Exception($message);
                    }
                }
                $couponfeed = $response['couponfeed'];
                $totalMatches = $couponfeed['TotalMatches'];
                $totalPages = $couponfeed['TotalPages'];
                $currentPage = $couponfeed['PageNumberRequested'];

                if ($totalMatches > 0) {
                    $a_links = $couponfeed['link'];
                    foreach ($a_links as $key => $link) {
                        $description = isset($link['offerdescription']) ? $link['offerdescription'] : '';
                        $start_date = isset($link['offerstartdate']) ? $link['offerstartdate'] : '';
                        $end_date = isset($link['offerenddate']) ? $link['offerenddate'] : '';
                        $coupon_code = isset($link['couponcode']) ? $link['couponcode'] : '';
                        $coupon_restriction = isset($link['couponrestriction']) ? $link['couponrestriction'] : '';
                        $click_url = isset($link['clickurl']) ? $link['clickurl'] : '';
                        $impression_pixel = isset($link['impressionpixel']) ? $link['impressionpixel'] : '';
                        $advertiser_id = isset($link['advertiserid']) ? $link['advertiserid'] : '';
                        $advertiser_name = isset($link['advertisername']) ? $link['advertisername'] : '';
                        $network_id = isset($link['networkid']) ? $link['networkid'] : '';
                        $promotion_types = isset($link['promotiontypes']) ? $link['promotiontypes'] : '';
                        $promotion_type = isset($promotion_types['promotiontype']) ? $promotion_types['promotiontype'] : '';
                        $promotion_type_code = isset($promotion_types['promotiontype_attr']) ? $promotion_types['promotiontype_attr']['id'] : '0';

                        /*
                        <promotiontype id="2">Buy One / Get One</promotiontype>
                        <promotiontype id="3">Clearance</promotiontype>
                        <promotiontype id="4">Combination Savings</promotiontype>
                        <promotiontype id="14">Deal of the Day/Week</promotiontype>
                        <promotiontype id="13">Free Delivery</promotiontype>
                        <promotiontype id="6">Free Trial / Usage</promotiontype>
                        <promotiontype id="8">Friends and Family</promotiontype>
                        <promotiontype id="1">General Promotion</promotiontype>
                        <promotiontype id="9">Gift with Purchase</promotiontype>
                        <promotiontype id="10">Other</promotiontype>
                        <promotiontype id="11">Percentage off</promotiontype>
                        <promotiontype id="12">Pounds amount off</promotiontype>
                         */

                        switch ($promotion_type_code) {
                            case '2':
                                $type = \Oara\Utilities::OFFER_TYPE_FREE_ARTICLE;
                                break;
                            case 3:
                            case 4:
                            case 14:
                            case 6:
                            case 8:
                            case 1:
                            case 10:
                                $type = \Oara\Utilities::OFFER_TYPE_VOUCHER;
                                break;
                            case 13:
                                // <promotiontype id="13">Free Delivery</promotiontype>
                                $type = \Oara\Utilities::OFFER_TYPE_FREE_SHIPPING;
                                break;
                            case 9:
                                // <promotiontype id="9">Gift with Purchase</promotiontype>
                                $type = \Oara\Utilities::OFFER_TYPE_FREE_ARTICLE;
                                break;
                            case 11:
                                // <promotiontype id="11">Percentage off</promotiontype>
                                $type = \Oara\Utilities::OFFER_TYPE_DISCOUNT;
                                break;
                            case 12:
                                // <promotiontype id="12">Pounds amount off</promotiontype>
                                $type = \Oara\Utilities::OFFER_TYPE_DISCOUNT;
                                break;
                            default:
                                $type = \Oara\Utilities::OFFER_TYPE_VOUCHER;
                        }

                        $arrResult[] = array(
                            'promotionId' => '',
                            'advertiser_id' => $advertiser_id,
                            'advertiser_name' => $advertiser_name,
                            'code' => $coupon_code,
                            'description' => $description,
                            'restriction' => $coupon_restriction,
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'tracking' => $click_url,
                            'type' => $type
                        );
                    }
                }
                if ($currentPage >= $totalPages) {
                    // End of results
                    break;
                }
                $currentPage++;
            }

            return $arrResult;


        } catch (\Exception $e) {
            echo "LinkShare getVouchers error:".$e->getMessage()."\n ";
            throw new \Exception($e);
        }
        return $vouchers;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getPaymentHistory()
    {
        $paymentHistory = array();
        $past = new \DateTime ("2013-01-01 00:00:00");
        $now = new \DateTime ();

        foreach ($this->_siteList as $site) {

            $interval = $past->diff($now);
            $numberYears = (int)$interval->format('%y') + 1;
            $auxStartDate = clone $past;

            for ($i = 0; $i < $numberYears; $i++) {

                $auxEndData = clone $auxStartDate;
                $auxEndData = $auxEndData->add(new \DateInterval('P1Y'));

                $url = "https://reportws.linksynergy.com/downloadreport.php?bdate=" . $auxStartDate->format("Ymd") . "&edate=" . $auxEndData->format("Ymd") . "&token=" . $site->secureToken . "&nid=" . $this->_nid . "&reportid=1";
                $result = \file_get_contents($url);
                if (\preg_match("/You cannot request/", $result)) {
                    throw new \Exception ("Reached the limit");
                }
                $paymentLines = \str_getcsv($result, "\n");
                $number = \count($paymentLines);
                for ($j = 1; $j < $number; $j++) {
                    $paymentData = \str_getcsv($paymentLines [$j], ",");
                    $obj = array();
                    $date = \DateTime::createFromFormat("Y-m-d", $paymentData [1]);
                    $obj ['date'] = $date->format("Y-m-d H:i:s");
                    $obj ['value'] = \Oara\Utilities::parseDouble($paymentData [5]);
                    $obj ['method'] = "BACS";
                    $obj ['pid'] = $paymentData [0];
                    $paymentHistory [] = $obj;
                }

                $auxStartDate->add(new \DateInterval('P1Y'));
            }
        }

        return $paymentHistory;
    }


    /**
     * Get a remote url content using Curl
     * @param $url
     * @return bool|string
     */
    private function getRemoteUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);                 // 5 minutes timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}
