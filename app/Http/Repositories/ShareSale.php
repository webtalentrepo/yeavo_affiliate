<?php


namespace App\Http\Repositories;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

class ShareSale
{
    /**
     * @var object
     */
    private $_network = null;
    private $_username = '';
    private $_password = '';
    private $_apiClient = null;
    protected $_tracking_parameter = 'afftrack';
    private $_idSite = '';
    private $_logged = false;

    public function __construct()
    {
        $this->_network = new \Oara\Network\Publisher\ShareASale;
        $this->_username = 'dbbrock1';
        $this->_password = 'DimaPassWord136';
        $this->_idSite = '1142939';

        $this->login($this->_username, $this->_password, $this->_idSite);
    }

    public function login($username, $password, $idSite = '')
    {

        $this->_logged = false;
        if (is_null($username) || is_null($password)) {

            return false;
        }

        $this->_username = $username;
        $this->_password = $password;
        $this->_idSite = $idSite;
        $credentials = array();
        $credentials["apiToken"] = $this->_username;
        $credentials["apiSecret"] = $this->_password;
        $credentials["affiliateId"] = $this->_idSite;
        $this->_network->login($credentials);

        if ($this->_network->checkConnection()) {
            $this->_logged = true;
        }

        return $this->_logged;
    }

    /**
     * @return bool
     */
    public function checkLogin()
    {
        return $this->_logged;
    }

    public function getMerchants($params)
    {
        $parameter = '';

        return $this->_network->getMerchantList($parameter);
    }
}
