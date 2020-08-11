<?php
namespace Oara\Network\Publisher;

class PepperJamApi extends \Oara\Network
{
    private $_api_key;

    private $BASE_PATH = "https://api.pepperjamnetwork.com/20120402/publisher";

    public function __construct($apiKey)
    {
        $this->_api_key = $apiKey;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getNeededCredentials()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = [];

        try {
            $MERCHANT_LIST_PATH = "{$this->BASE_PATH}/advertiser";

            $client = $this->buildClient($MERCHANT_LIST_PATH);

            $response = json_decode($this->execClientCall($client));

            $merchants = $this->parseMerchants($response->data);

            $nextPageUrl = null;
            if (isset($response->meta->pagination->next->href)) {
                $nextPageUrl = $response->meta->pagination->next->href;
            }
            
            // iterate through pages
            if ($nextPageUrl) {
                $hasNextPage = true;
                while ($hasNextPage) {
                    $response = json_decode($this->getNextPage($nextPageUrl));

                    $nextPageUrl = null;
                    if (isset($response->meta->pagination->next->href)) {
                        $nextPageUrl = $response->meta->pagination->next->href;
                    }
                    
                    $merchants = array_merge($merchants, $this->parseMerchants($response->data));

                    if (!$nextPageUrl) {
                        $hasNextPage = false;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('[php-oara][Oara][Network][Publisher][PepperJamApi][getMerchantList][Exception] ' . $e->getMessage());
        }

        return $merchants;
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
        $transactions = [];

        try {
            $TRANSACTIONS_LIST_PATH = "{$this->BASE_PATH}/report/transaction-details";

            $params = [
                "startDate" => $dStartDate->format("Y-m-d"),
                "endDate" => $dEndDate->format("Y-m-d")
            ];

            $client = $this->buildClient($TRANSACTIONS_LIST_PATH, $params);

            $response = json_decode($this->execClientCall($client));

            $transactions = $this->parseTransactions($response->data);

            $nextPageUrl = null;
            if (isset($response->meta->pagination->next->href)) {
                $nextPageUrl = $response->meta->pagination->next->href;
            }
            
            // iterate through pages
            if ($nextPageUrl) {
                $hasNextPage = true;
                while ($hasNextPage) {
                    $response = json_decode($this->getNextPage($nextPageUrl));

                    $nextPageUrl = null;
                    if (isset($response->meta->pagination->next->href)) {
                        $nextPageUrl = $response->meta->pagination->next->href;
                    }
                    
                    $transactions = array_merge($transactions, $this->parseTransactions($response->data));

                    if (!$nextPageUrl) {
                        $hasNextPage = false;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('[php-oara][Oara][Network][Publisher][PepperJamApi][getTransactionList][Exception] ' . $e->getMessage());
        }

        return $transactions;
    }

    /**
     * @return array
     */
    public function getPaymentHistory()
    {
        return [];
    }

    /**
     * @param $paymentId
     * @return array
     */
    public function paymentTransactions($paymentId)
    {
        return [];
    }


    // PRIVATE

    private function buildClient($basePath, $params = [])
    {
        $client = curl_init();

        $params["apiKey"] = $this->_api_key;
        $params["format"] = "json";
        $url = $basePath . "?" . http_build_query($params);

        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        return $client;
    }

    private function execClientCall($client)
    {
        $response = curl_exec($client);
        curl_close($client);
        return $response;
    }

    private function getNextPage($url)
    {
        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($client);
        curl_close($client);
        return $response;
    }

    private function parseMerchants($rawMerchants)
    {
        return array_map(function ($rawMerchant) {
            $merchant = [];

            $merchant["cid"] = $rawMerchant->id;
            $merchant["name"] = $rawMerchant->name;
            $merchant["url"] = $rawMerchant->website;
            $merchant["status"] = $rawMerchant->status;
            $merchant["application_date"] = $rawMerchant->join_date;

            return $merchant;
        }, $rawMerchants);
    }

    private function parseTransactions($rawTransactions)
    {
        return array_map(function ($rawTransaction) {
            $transaction = [];

            $transaction["unique_id"] = $rawTransaction->transaction_id;
            $transaction["order_id"] = $rawTransaction->order_id;
            $transaction["creative_type"] = $rawTransaction->creative_type;
            $transaction["commission"] = \Oara\Utilities::parseDouble($rawTransaction->commission);
            $transaction["amount"] = \Oara\Utilities::parseDouble($rawTransaction->sale_amount);
            $transaction["type"] = $rawTransaction->type;
            $transaction["date"] = $rawTransaction->date;
            // status
            switch ($rawTransaction->status) {
                case "pending":
                case "delayed":
                    $transaction["status"] = \Oara\Utilities::STATUS_PENDING;
                    break;
                case "lock":
                    $transaction["status"] = \Oara\Utilities::STATUS_CONFIRMED;
                    break;
                case "unconfirmed":
                    $transaction["status"] = \Oara\Utilities::STATUS_DECLINED;
                    break;
                case "paid":
                    $transaction["status"] = \Oara\Utilities::STATUS_PAID;
                    break;
                default:
                    $transaction["status"] = $rawTransaction->status;
                    break;
            }
            
            $transaction["new_to_file"] = $rawTransaction->new_to_file;
            $transaction["sub_type"] = $rawTransaction->sub_type;
            $transaction["custom_id"] = $rawTransaction->sid;
            $transaction["program_name"] = $rawTransaction->program_name;
            $transaction["program_id"] = $rawTransaction->program_id;

            return $transaction;
        }, $rawTransactions);
    }
}
