<?php


namespace App\Http\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Oara\Network\Publisher\ShareASale;

require_once __DIR__ . '/php-oara/vendor/autoload.php';

class ShareSale
{
    protected $_tracking_parameter = 'afftrack';
    /**
     * @var object
     */
    private $_network = null;
    private $_username = '';
    private $_password = '';
    private $_apiClient = null;
    private $_idSite = '';
    private $_logged = false;

    public function __construct()
    {
        $this->_network = new ShareASale;
        $this->_username = config('services.share_a_sale.key');
        $this->_password = config('services.share_a_sale.sec');
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
        $credentials = [];
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
        $parameter = '&Category=' . $params['category'];

        return $this->_network->getMerchantList($parameter);
    }

    public function dataInsertFromAPI($link)
    {
        $cat = [
            'acc', 'art', 'auction', 'bus', 'car', 'clo', 'com', 'cpu', 'dating', 'domain', 'edu', 'fam', 'fin', 'free',
            'fud', 'gif', 'gourmet', 'green', 'hea', 'hom', 'hosting', 'ins', 'job', 'legal', 'lotto', 'mal', 'mar', 'med',
            'military', 'mov', 'rec', 'res', 'search', 'spf', 'toy', 'tvl', 'web', 'webmaster', 'weddings'
        ];

        for ($i = 0; $i < count($cat); $i++) {
            $parameter = '&Category=' . $cat[$i];

            $data = $this->_network->getMerchantList($parameter);

            if ($data && isset($data['merchantSearchrecord'])) {
                if (count($data['merchantSearchrecord']) > 0) {
                    $re = $data['merchantSearchrecord'];
                    foreach ($re as $row) {
                        if (!isset($row['merchantid']) || $row['merchantid'] == '') {
                            continue;
                        }

                        $scout = Product::where('site_id', $row['merchantid'])
                            ->where('network', $link)
                            ->first();

                        if ($scout) {
                            if ($scout->deleted_flag || $scout->edited_flag) {
                                continue;
                            }
                        } else {
                            $scout = new Product();
                        }

                        $name = '';
                        if (isset($row['organization'])) {
                            $name = is_array($row['organization']) ? json_encode($row['organization']) : $row['organization'];
                        }

                        if (isset($row['www'])) {
                            $name .= ($name != '' ? ' - ' : '') . (is_array($row['www']) ? json_encode($row['www']) : $row['www']);
                        }

                        $comm = 0;
                        if (isset($row['salecomm'])) {
                            $comm = is_array($row['salecomm']) ? json_encode($row['salecomm']) : $row['salecomm'];
                        } elseif (isset($row['leadcomm'])) {
                            $comm = is_array($row['leadcomm']) ? json_encode($row['leadcomm']) : $row['leadcomm'];
                        } elseif (isset($row['hitcomm'])) {
                            $comm = is_array($row['hitcomm']) ? json_encode($row['hitcomm']) : $row['hitcomm'];
                        }

                        $comm_text = '';
                        if (isset($row['commissiontext'])) {
                            $comm_text = is_array($row['commissiontext']) ? json_encode($row['commissiontext']) : $row['commissiontext'];
                        }

                        $c_unit = '';
                        if (isset($comm_text) && strpos($comm_text, '$') !== false) {
                            $c_unit = 'USD';
                        }

                        if (isset($comm_text) && strpos($comm_text, '%') !== false) {
                            $c_unit = '%';
                        }

                        $p_lank = 0;
                        if (isset($row['powerranktop100'])) {
                            if ($row['powerranktop100'] != 'No') {
                                $p_lank = 1;
                            }
                        }

                        $scout->network = $link;
                        $scout->category = isset($row['category']) ? (is_array($row['category']) ? json_encode($row['category']) : $row['category']) : '';
                        $scout->child_category = isset($row['reversalrate30day']) ? (is_array($row['reversalrate30day']) ? json_encode($row['reversalrate30day']) : $row['reversalrate30day']) : '';
                        $scout->full_category = isset($row['category']) ? (is_array($row['category']) ? json_encode($row['category']) : $row['category']) : '';
                        $scout->site_id = (is_array($row['merchantid']) ? json_encode($row['merchantid']) : $row['merchantid']);
                        $scout->program_url = isset($row['www']) ? (is_array($row['www']) ? json_encode($row['www']) : $row['www']) : '';
                        $scout->popular_rank = isset($row['avecomm30day']) ? (is_array($row['avecomm30day']) ? json_encode($row['avecomm30day']) : $row['avecomm30day']) : 0;
                        $scout->p_title = $name;
                        $scout->p_description = $comm_text;
                        $scout->p_commission = $comm;
                        $scout->p_commission_unit = $c_unit;
                        $scout->p_gravity = $p_lank;
                        $scout->seven_day_epc = isset($row['epc7day']) ? (is_array($row['epc7day']) ? json_encode($row['epc7day']) : $row['epc7day']) : '';
                        $scout->three_month_epc = isset($row['epc30day']) ? (is_array($row['epc30day']) ? json_encode($row['epc30day']) : $row['epc30day']) : '';
                        $scout->earning_uint = 'USD';
                        $scout->p_percent_sale = isset($row['avesale30day']) ? (is_array($row['avesale30day']) ? json_encode($row['avesale30day']) : $row['avesale30day']) : '';
                        $scout->deleted_flag = 0;
                        $scout->edited_flag = 0;

                        $scout->save();
                    }
                }
            }
        }
    }
}
