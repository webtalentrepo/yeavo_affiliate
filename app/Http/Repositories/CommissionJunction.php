<?php


namespace App\Http\Repositories;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

use App\Models\ChildProduct;
use App\Models\Product;
use Exception;
use Oara\Network\Publisher\CommissionJunctionGraphQL;

class CommissionJunction
{
    private $_network = null;
    private $_username = '';
    private $_password = '';
    private $_passwordApi = '';
    private $_publisher_id = '';
    private $_logged = false;

    public function __construct()
    {
        $this->_network = new CommissionJunctionGraphQL();
        $this->_username = 'dbswim13@yahoo.com';
        $this->_password = config('services.cj_access_token');
        $this->_passwordApi = config('services.cj_access_token');
        $this->_publisher_id = '2632470';

        if (trim($this->_publisher_id) != '') {
            $this->addAllowedSite($this->_publisher_id);
        }

        try {
            $this->login($this->_username, $this->_password, $this->_publisher_id);
        } catch (Exception $e) {
        }
    }

    public function addAllowedSite($idSite)
    {
        if (trim($idSite) != '') {
            $this->_network->addAllowedSite($idSite);
        }
    }

    /**
     * @param $username
     * @param $password
     * @param $idSite
     * @return bool
     * @throws Exception
     */
    public function login($username, $password, $idSite)
    {
        $this->_logged = false;
        if (is_null($username) && is_null($password)) {
            return false;
        }
        $this->_username = $username;
        $this->_password = $password;
        $this->_passwordApi = $password;
        $this->_publisher_id = $idSite;
        $credentials = [];
        $credentials["user"] = $this->_username;
        $credentials["password"] = $this->_password;
        $credentials["apipassword"] = $this->_passwordApi;
        $credentials["id_site"] = $idSite;

        if (trim($idSite) != '') {
            $this->addAllowedSite($idSite);
        }

        $this->_network->login($credentials);
        if ($this->_network->checkConnection()) {
            $this->_logged = true;
        }

        return $this->_logged;
    }

    public function checkLogin()
    {
        return $this->_logged;
    }

    public function getMerchants($params)
    {
        return $this->_network->getMerchantList($params);
    }

    public function getProductDetails($aid)
    {
        //, sincePostingDate: "2020-06-08T00:00:00Z", beforePostingDate: "2020-07-08T00:00:00Z"
        $query = '{
    products(companyId: "' . $this->_publisher_id . '", limit: 1000, partnerIds: ["' . $aid . '"]) {
      resultList {
        imageLink,
        link,
        adId,
        advertiserId,
        brand,
        catalogId,
        id,
        title,
        description,
        price {
          amount,
          currency
        },
        salePrice {
          amount,
          currency
        },
        salePriceEffectiveDateEnd,
        salePriceEffectiveDateStart,
        sourceFeedType,
        targetCountry,
        itemListName
      }
    }
  }';

        return $this->postGraphQL('https://ads.api.cj.com/query', $query);
    }

    private function postGraphQL($url, $query)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $this->_passwordApi]);

        $curl_results = curl_exec($ch);
        curl_close($ch);

        return json_decode($curl_results, true);
    }

    public function saveDataToTable($re, $link)
    {
        foreach ($re as $key => $row) {
            if (!isset($row['advertiser-id'])) {
                continue;
            }

            $sale = '0';
            $c_name = '';
            if (isset($row['actions'])) {
                if (isset($row['actions']['action'])) {
                    if (isset($row['actions']['action']['commission'])) {
                        if (isset($row['actions']['action']['name'])) {
                            $c_name = $row['actions']['action']['name'];
                        }

                        if (isset($row['actions']['action']['commission']['default'])) {
                            $sale = $row['actions']['action']['commission']['default'];
                        }
                    } else {
                        if (isset($row['actions']['action'][0]) && isset($row['actions']['action'][0]['commission'])) {
                            $sale = $row['actions']['action'][0]['commission']['default'];

                            if (isset($row['actions']['action'][0]['name'])) {
                                $c_name = $row['actions']['action'][0]['name'];
                            }
                        }
                    }
                }
            }

            $aid = '';
            if (!is_array($row['advertiser-id'])) {
                $aid = $row['advertiser-id'];
            } else {
                if (is_array($row['advertiser-id'])) {
                    if (sizeof($row['advertiser-id']) > 0) {
                        $aid = $row['advertiser-id'][0];
                    }
                } else {
                    $aid = $row['advertiser-id'];
                }
            }

            if (isset($row['advertiser-name'])) {
                if (is_array($row['advertiser-name'])) {
                    if (sizeof($row['advertiser-name']) > 0) {
                        $name = $row['advertiser-name'][0];
                    } else {
                        $name = $c_name;
                    }
                } else {
                    $name = $row['advertiser-name'];
                }
            } else {
                $name = $c_name;
            }

            $program_url = '';
            if (isset($row['program-url'])) {
                if (is_array($row['program-url'])) {
                    if (sizeof($row['program-url']) > 0) {
                        $program_url = $row['program-url'][0];
                    }
                } else {
                    $program_url = $row['program-url'];
                }
            }

            $commission = (float)$sale;
            $c_unit_ary = explode($commission, $sale);
            $c_unit = '%';

            if (isset($c_unit_ary[1]) && trim($c_unit_ary[1]) == '%') {
                $c_unit = '%';
            } else {
                $c_unit_ary = explode(' ', $sale);

                if (isset($c_unit_ary[1]) && !is_nan($c_unit_ary[1] * 1) && is_numeric($c_unit_ary[1] * 1)) {
                    $c_unit = $c_unit_ary[0];
                }
            }

            $scout = Product::where('site_id', $aid)
                ->where('network', $link)
                ->first();

            if ($scout) {
                if ($scout->deleted_flag || $scout->edited_flag) {
                    continue;
                }
            } else {
                $scout = new Product();
            }

            $parent = '';
            $child = '';
            if (isset($row['primary-category']['parent'])) {
                if (is_array(isset($row['primary-category']['parent']))) {
                    if (sizeof($row['primary-category']['parent']) > 0) {
                        $parent = $row['primary-category']['parent'][0];
                    }
                } else {
                    $parent = $row['primary-category']['parent'];
                }
            }

            if (isset($row['primary-category']['child'])) {
                if (is_array(isset($row['primary-category']['child']))) {
                    if (sizeof($row['primary-category']['child']) > 0) {
                        $child = $row['primary-category']['child'][0];
                    }
                } else {
                    $child = $row['primary-category']['child'];
                }
            }

            $parent = is_array($parent) ? json_encode($parent) : $parent;
            $child = is_array($child) ? json_encode($child) : $child;

            $scout->network = $link;
            $scout->category = $parent;
            $scout->child_category = $child;
            $scout->full_category = $parent != '[]' ? ($parent . '/' . $child) : $child;
            $scout->site_id = is_array($aid) ? json_encode($aid) : $aid;
            $scout->program_url = $program_url;
            $scout->popular_rank = isset($row['network-rank']) ? (is_array($row['network-rank']) ? json_encode($row['network-rank']) : $row['network-rank']) : 0;
            $scout->p_title = is_array($name) ? json_encode($name) : $name;
            $scout->p_description = is_array($c_name) ? json_encode($c_name) : $c_name;
            $scout->p_commission = is_array($commission) ? json_encode($commission) : $commission;
            $scout->p_commission_unit = is_array($c_unit) ? json_encode($c_unit) : $c_unit;
            $scout->p_gravity = isset($row['network-rank']) ? (is_array($row['network-rank']) ? json_encode($row['network-rank']) : $row['network-rank']) : 0;
            $scout->seven_day_epc = isset($row['seven-day-epc']) ? (is_array($row['seven-day-epc']) ? json_encode($row['seven-day-epc']) : $row['seven-day-epc']) : '';
            $scout->three_month_epc = isset($row['three-month-epc']) ? (is_array($row['three-month-epc']) ? json_encode($row['three-month-epc']) : $row['three-month-epc']) : '';
            $scout->earning_uint = 'USD';
            $scout->p_percent_sale = ($c_unit == '%') ? (is_array($commission) ? json_encode($commission) : $commission) : 0;
            $scout->deleted_flag = 0;
            $scout->edited_flag = 0;

            $scout->save();
        }
    }

    public function setChildProducts($re, $parent_id)
    {
        if ($re && isset($re['data'])) {
            if (isset($re['data']['products']) && isset($re['data']['products']['resultList'])) {
                $data = $re['data']['products']['resultList'];
                if (sizeof($data) > 0) {
                    foreach ($data as $key => $row) {
                        if (!isset($row['advertiserId']) || !isset($row['id'])) {
                            continue;
                        }

                        $product = ChildProduct::where('parent_id', $parent_id)
                            ->where('product_id', $row['id'])
                            ->where('advertiser_id', $row['advertiserId'])
                            ->first();

                        if ($product) {
                            if ($product->deleted_flag || $product->edited_flag) {
                                continue;
                            }
                        } else {
                            $product = new ChildProduct();
                        }

                        $product->parent_id = $parent_id;
                        $product->product_id = $row['id'];
                        $product->advertiser_id = $row['advertiserId'];
                        $product->ad_id = isset($row['adId']) ? (is_array($row['adId']) ? json_encode($row['adId']) : $row['adId']) : '';
                        $product->source_feed_type = isset($row['sourceFeedType']) ? (is_array($row['sourceFeedType']) ? json_encode($row['sourceFeedType']) : $row['sourceFeedType']) : '';
                        $product->title = isset($row['title']) ? (is_array($row['title']) ? json_encode($row['title']) : $row['title']) : '';
                        $product->description = isset($row['description']) ? (is_array($row['description']) ? json_encode($row['description']) : $row['description']) : '';
                        $product->target_country = isset($row['targetCountry']) ? (is_array($row['targetCountry']) ? json_encode($row['targetCountry']) : $row['targetCountry']) : '';
                        $product->brand = isset($row['brand']) ? (is_array($row['brand']) ? json_encode($row['brand']) : $row['brand']) : '';
                        $product->link = isset($row['link']) ? (is_array($row['link']) ? json_encode($row['link']) : $row['link']) : '';
                        $product->image_link = isset($row['imageLink']) ? (is_array($row['imageLink']) ? json_encode($row['imageLink']) : $row['imageLink']) : '';
                        $product->p_amount = isset($row['price']) && isset($row['price']['amount']) ? (is_array($row['price']['amount']) ? json_encode($row['price']['amount']) : $row['price']['amount']) : 0;
                        $product->p_currency = isset($row['price']) && isset($row['price']['currency']) ? (is_array($row['price']['currency']) ? json_encode($row['price']['currency']) : $row['price']['currency']) : '';
                        $product->s_amount = isset($row['salePrice']) && isset($row['salePrice']['amount']) ? (is_array($row['salePrice']['amount']) ? json_encode($row['salePrice']['amount']) : $row['salePrice']['amount']) : 0;
                        $product->s_currency = isset($row['salePrice']) && isset($row['salePrice']['currency']) ? (is_array($row['salePrice']['currency']) ? json_encode($row['salePrice']['currency']) : $row['salePrice']['currency']) : '';
                        $product->catalog_id = isset($row['catalogId']) ? (is_array($row['catalogId']) ? json_encode($row['catalogId']) : $row['catalogId']) : '';
                        $product->deleted_flag = 0;
                        $product->edited_flag = 0;

                        $product->save();
                    }
                }
            }
        }
    }
}
