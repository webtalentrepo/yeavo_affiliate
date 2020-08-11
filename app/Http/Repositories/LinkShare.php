<?php


namespace App\Http\Repositories;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

class LinkShare
{
    private $_network = null;
    private $_username = '';
    private $_password = '';
    private $_apiClient = null;
    protected $_tracking_parameter = '';
    private $_idSite = '';
    private $_logged = false;

    public function __construct()
    {
        $this->_network = new \Oara\Network\Publisher\LinkShare;
        $this->_username = 'deadbeat';
        $this->_password = '2m1K2i4oel!#';
        $this->_idSite = '3706879';

        $this->login($this->_username, $this->_password, $this->_idSite);
    }

    public function login($username, $password, $idSite = ''): bool
    {
        $this->_logged = false;

        if (is_null($username) || is_null($password)) {

            return false;
        }
        $this->_username = $username;
        $this->_password = $password;

        $credentials = array();
        $credentials["user"] = $this->_username;
        $credentials["password"] = $this->_password;
        $credentials["idSite"] = $idSite;
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
        try {
            return $this->_network->getMerchantList($params);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
