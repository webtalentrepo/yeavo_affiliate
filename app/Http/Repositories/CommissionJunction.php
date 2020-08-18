<?php


namespace App\Http\Repositories;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

//use App\Http\Repositories\Affiliates\Merchant;
use App\Models\Product;
use Oara\Network\Publisher\CommissionJunctionGraphQL;
use Exception;

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
        $credentials = array();
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
//        $arrResult = [];
        //        foreach ($merchantList as $merchant) {
//            if ($merchant['status'] == 'Setup') {
//                // Ignore setup programs not yet active
//                continue;
//            }
//            try {
//                $Merchant = Merchant::createInstance();
//                $Merchant->merchant_ID = $merchant['cid'];
//                $Merchant->name = $merchant['name'];
//                $Merchant->url = $merchant['url'];
//                if ($merchant['status'] == 'Active') {
//                    $Merchant->status = $merchant['relationship_status'];
//                } else {
//                    $Merchant->status = $merchant['status'];
//                }
//                $arrResult[] = $Merchant;
//            } catch (Exception $e) {
//            }
//        }

        return $this->_network->getMerchantList($params);
    }

    public function addAllowedSite($idSite)
    {
        if (trim($idSite) != '') {
            $this->_network->addAllowedSite($idSite);
        }
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
}
