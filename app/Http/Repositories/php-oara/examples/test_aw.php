<?php

use Oara\Network\Publisher\AffiliateWindow;

include_once(dirname(__FILE__) . '/../settings.php');

$network = new AffiliateWindow();
$credentialsNeeded = $network->getNeededCredentials();
$credentials = [];
$credentials["user"] = "";
$credentials["password"] = "";
$credentials['accountid'] = "";
$credentials['apipassword'] = "";
$credentials['currency'] = null;

$network->login($credentials);
if ($network->checkConnection()) {
    //$network->getPaymentHistory();
    $merchantList = $network->getMerchantList();
    $startDate = new DateTime('2017-04-21');
    $endDate = new DateTime('2017-04-24');
    $transactionList = $network->getTransactionList($merchantList, $startDate, $endDate);
    //var_dump($transactionList);
} else {
    echo "Network credentials not valid \n";
}
