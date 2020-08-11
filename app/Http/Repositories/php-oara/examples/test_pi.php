<?php
include_once (dirname(__FILE__) . '/../settings.php');

$network = new \Oara\Network\Publisher\Publicidees();
$credentialsNeeded = $network->getNeededCredentials();
$credentials = array();
$credentials["user"] = "";
$credentials["password"] = "";
$credentials['accountid'] = "";
$credentials['apipassword'] = "";
$credentials['currency'] = null;

try {
    $network->login($credentials);
    if ($network->checkConnection()) {
        //$network->getPaymentHistory();
        //$merchantList = array();
        $merchantList = $network->getMerchantList();
        $startDate = new \DateTime('2017-04-21');
        $endDate = new \DateTime('2017-04-24');
        $transactionList = $network->getTransactionList($merchantList, $startDate, $endDate);
        var_dump($transactionList);
    } else {
        echo "Network credentials not valid \n";
    }
} catch (\Exception $e) {
    //echo "stepE ";
    echo "<br><br>errore: ".$e->getMessage()."<br><br>";
    var_dump($e->getTraceAsString());
    //throw new \Exception($e);
}