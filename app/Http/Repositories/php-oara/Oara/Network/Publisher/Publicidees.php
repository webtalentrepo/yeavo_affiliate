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
 * API Class
 *
 * @author     Carlos Morillo Merino
 * @category   Publicidees
 * @copyright  Fubra Limited
 * @version    Release: 01.00
 *
 */
class Publicidees extends \Oara\Network
{
    private $_client = null;
    private $_user = null;
    private $_password = null;

    /**
     * @param $credentials
     */
    public function login($credentials)
    {

        $this->_user = $credentials['user'];
        $this->_password = $credentials['password'];
        $this->_client = new \Oara\Curl\Access($credentials);
        /*
                $loginUrl = 'http://es.publicideas.com/logmein.php';
                $valuesLogin = array(new \Oara\Curl\Parameter('loginAff', $user),
                    new \Oara\Curl\Parameter('passAff', $password),
                    new \Oara\Curl\Parameter('userType', 'aff')
                );

                $urls = array();
                $urls[] = new \Oara\Curl\Request($loginUrl, $valuesLogin);
                $exportReport = $this->_client->post($urls);
                $result = \json_decode($exportReport[0]);
                $loginUrl = 'http://publisher.publicideas.com/entree_affilies.php';
                $valuesLogin = array(new \Oara\Curl\Parameter('login', $result->login),
                    new \Oara\Curl\Parameter('pass', $result->pass),
                    new \Oara\Curl\Parameter('submit', 'Ok'),
                    new \Oara\Curl\Parameter('h', $result->h)
                );
                $urls = array();
                $urls[] = new \Oara\Curl\Request($loginUrl, $valuesLogin);
                $this->_client->post($urls);
        */
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
     * @return bool
     */
    public function checkConnection()
    {
        // Just check for valid credentiale
        if (!empty($this->_user) && !empty($this->_password)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getMerchantList()
    {
        $merchants = array();

        $obj = array();
        $obj['cid'] = 1;
        $obj['name'] = "Publicidees";
        $merchants[] = $obj;
        return $merchants;
    }

    /**
     *
     * ActionStatus = Status of Action
     * 0 = action refused
     * 1 = action pending
     * 2 = action approved
     * ActionType = Type of action
     * 3 = sales-based remuneration
     * 4 = form-based remuneration
     * See: https://performance.timeonegroup.com/PDF/en_US/TimeOne_APISUBID_EN.pdf
     *
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

            //$response = file_get_contents ('http://api.publicidees.com/subid.php5?p=51238&k=c534b0f5dcdddb5f56caa70e4ff3ec3b');
            /*
                        echo "usr: ".$this->_user."<br>";
                        echo "pwd ".$this->_password."<br>";
                        echo "url: ".('http://api.publicidees.com/subid.php5?p='.$this->_user.'&k='.$this->_password.'')."<br>";
            */
            //$response = file_get_contents ('http://api.publicidees.com/subid.php5?p='.$this->_user.'&k='.$this->_password.'&dd='.$dStartDate->format('Y-m-d').'&df='.$dEndDate->format('Y-m-d'));
            //$response = file_get_contents ('http://api.publicidees.com/subid.php5?p='.$this->_user.'&k='.$this->_password.'&dd=2019-09-01&df='.$dEndDate->format('Y-m-d'));

            $url = 'http://api.publicidees.com/subid.php5?p='.$this->_user.'&k='.$this->_password.'&dd='.$dStartDate->format('Y-m-d').'&df='.$dEndDate->format('Y-m-d');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, false);
            $response = curl_exec($ch);
            curl_close($ch);

            //error messages returned by api call:
            //1) You reached the limit of 3 call(s) in the last 900 seconds (15 min). Try again later. Thank you
            //2) Wrong information on parameter : p, k

            if (strpos($response, 'reached the limit') !== false ||
                strpos($response, 'Wrong') !== false)
                throw new \Exception($response);

            $xml_encode = utf8_encode($response);

            //Convert the XML string into an SimpleXMLElement object.
            $ids = \simplexml_load_string($xml_encode, "SimpleXMLElement", \LIBXML_NOCDATA);

            //echo "<br><br>RESPONSE<br><br>";
            //var_dump($response);


            /*  XML PER TEST */
            /*
                        $response =
                            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                            <partner id='51238'>
                                <program id='390'>
                                    <name>
                                        Wineandco
                                    </name>
                                    <action id=\"1826271-606458\" SubID=\"\" ActionDate=\"2017-04-12 14:32:54\" ValidationDate=\"\" ActionStatus=\"2\" ActionType=\"3\" ProgramCommission=\"10.000%\" ActionCommission=\"17.833\" CartAmount=\"178.33\" ProgramComID=\"835377\" PartnerComID=\"835018\" Title=\"Vente_NouveauClient\" ProgramCurrency=\"EUR\" Device=\"desktop\" />
                                </program>
                                <program id='546'>
                                    <name>
                                        <![CDATA[SexyAvenue FR]]>
                                    </name>
                                    <action id=\"24865487\" SubID=\"\" ActionDate=\"2017-04-12 16:44:10\" ValidationDate=\"\" ActionStatus=\"2\" ActionType=\"3\" ProgramCommission=\"20.000%\" ActionCommission=\"22.238\" CartAmount=\"111.19\" ProgramComID=\"73146\" PartnerComID=\"73146\" Title=\"Vente\" ProgramCurrency=\"EUR\" Device=\"desktop\" />
                                    <action id=\"24865308\" SubID=\"\" ActionDate=\"2017-04-12 13:27:26\" ValidationDate=\"\" ActionStatus=\"1\" ActionType=\"3\" ProgramCommission=\"20.000%\" ActionCommission=\"3.334\" CartAmount=\"16.67\" ProgramComID=\"73146\" PartnerComID=\"73146\" Title=\"Vente\" ProgramCurrency=\"EUR\" Device=\"mobile\" />
                                    <action id=\"24865456\" SubID=\"\" ActionDate=\"2017-04-12 15:28:46\" ValidationDate=\"\" ActionStatus=\"2\" ActionType=\"3\" ProgramCommission=\"20.000%\" ActionCommission=\"8.486\" CartAmount=\"42.43\" ProgramComID=\"73146\" PartnerComID=\"73146\" Title=\"Vente\" ProgramCurrency=\"EUR\" Device=\"desktop\" />
                                </program>
                            </partner>
                        ";
            */
            //$ids = new \SimpleXMLElement($response);

            /*
                    foreach ($ids->program as $program) {
                        //echo var_dump($program);
                        echo "<br><br>";
                        echo "program id: ".($program[0]['id']);
                        echo "<br><br>";
                        echo "program name: ".($program->name);
                        echo "<br><br>";
                        echo "actions array: <br><br>";
                        //var_dump($program->action);
                        foreach ($program->action as $action) {
                            echo "id: ".$action['id']."<br>";
                            echo "SubID: ".$action['SubID']."<br>";
                            echo "id: ".$action['ActionDate']."<br>";
                            echo "ActionStatus: ".$action['ActionStatus']."<br>";
                            echo "ActionType: ".$action['ActionType']."<br>";
                            echo "ProgramCommission: ".$action['ProgramCommission']."<br>";
                            echo "ActionCommission: ".$action['ActionCommission']."<br>";
                            echo "CartAmount: ".$action['CartAmount']."<br>";
                            echo "ProgramComID: ".$action['ProgramComID']."<br>";
                            echo "PartnerComID: ".$action['PartnerComID']."<br>";
                            echo "Title: ".$action['Title']."<br>";
                            echo "ProgramCurrency: ".$action['ProgramCurrency']."<br>";
                            echo "Device: ".$action['Device']."<br>";
                            echo "----------------NEXT ACTION---------------------<br>";
                        }
                        echo "<br><br><br><br><br><br><br><br>";
                    }
            */
            // $i=0;
            foreach ($ids->program as $program) {
                foreach ($program->action as $action) {
                    //echo $action['ProgramComID']."<br>";
                    //var_dump($action);
                    /*
                    $i++;
                    if ($i<6)
                        echo "action[ActionDate]: ".$action['ActionDate']."<br>";
                    */
                    $transaction = Array();
                    $transaction['merchantId'] = $program[0]['id'];
                    //Order number
                    $transaction['unique_id'] = $action['id'];
                    //commission ID
                    $transaction['commission_id'] = $action['ProgramComID'];
                    $transaction['date'] = $action['ActionDate'];
                    $transaction["validation_date"] = $action['ValidationDate'];    // Future use - <PN>
                    $transaction['amount'] = $action['CartAmount'];
                    $transaction['program_commission'] = $action['ProgramCommission'];  // format "5.000%" or "0.000EUR" - Future use
                    $transaction['commission'] = $action['ActionCommission'];
                    $transaction['title'] = urldecode($action['Title']) ;
                    $transaction['currency'] = $action['ProgramCurrency'];
                    $transaction['custom_id'] = $action['SubID'];
                    $transaction['approved'] = false;
                    $transaction['status'] = null;
                    if ($action['ActionStatus'] == 0) {
                        $transaction['status'] = \Oara\Utilities::STATUS_DECLINED;
                    } else if  ($action['ActionStatus'] == 1) {
                        $transaction['status'] = \Oara\Utilities::STATUS_PENDING;
                    } else  if ($action['ActionStatus'] == 2) {
                        $transaction['status'] = \Oara\Utilities::STATUS_CONFIRMED;
                        $transaction['approved'] = true;
                    }
                    $totalTransactions[] = $transaction;
                }
            }


        } catch (\Exception $e) {
            echo PHP_EOL . "Publicidees - getTransactionList err: ".$e->getMessage().PHP_EOL;
            throw new \Exception($e->getMessage());
        }
        return $totalTransactions;

    }
}
