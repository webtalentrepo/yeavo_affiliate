<?php


namespace App\Http\Repositories;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

use App\Http\Repositories\Affiliates\Merchant;
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
}
