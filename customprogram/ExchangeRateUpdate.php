<?php
if ( isset($_SERVER["REMOTE_ADDR"]) )    {
    $ip=$_SERVER["REMOTE_ADDR"];
} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
    $ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
    $ip=$_SERVER["HTTP_CLIENT_IP"];
} 
$accesslist=false;
if ($ip == "103.1.217.72" ) $accesslist=true;	//www.cosmeparadise.com server
if ($ip == "27.121.64.109" ) $accesslist=true;	//analystsupporter.com server
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}

////////////////////////get into magento /////////////////////
set_time_limit(0);
ignore_user_abort();
error_reporting(E_ALL^E_NOTICE);
$_SVR = array();

$path_include = "../app/Mage.php";

// Include configuration file
if(!file_exists($path_include)) {
	exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
}
else {
	require_once $path_include;
}

// Get default store code
$default_store = Mage::app()->getStore();
$default_store_code = $default_store->getCode();

if (isset($_GET['store']) && ($_GET['store'] != "")) {
	$store = $_GET['store'];
}
else {
	$store = $default_store_code;
}
Mage::app($store);
//////////////////////////////////////
Mage::register("isSecureArea", 1);
///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write


$currency_from = "AUD";
/////////////////////Update HKD/////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.cosmeparadise.com/customprogram/paypal/currencyexchangerate.php?FromCurrency=AUD&ToCurrency=HKD");
$Update_Rate = curl_exec($ch);
curl_close($ch);

$currency_to = "HKD";
$Update_SQL = "UPDATE directory_currency_rate SET rate = ? WHERE currency_from = ? AND currency_to = ?";
try{
	$connection_write->query($Update_SQL, array($Update_Rate, $currency_from, $currency_to));
	echo "finished update ".$currency_to." rate to ".$Update_Rate."<br>";
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}

/////////////////////Update EUR/////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.cosmeparadise.com/customprogram/paypal/currencyexchangerate.php?FromCurrency=AUD&ToCurrency=EUR");
$Update_Rate = curl_exec($ch);
curl_close($ch);

$currency_to = "EUR";
$Update_SQL = "UPDATE directory_currency_rate SET rate = ? WHERE currency_from = ? AND currency_to = ?";
try{
	$connection_write->query($Update_SQL, array($Update_Rate, $currency_from, $currency_to));
	echo "finished update ".$currency_to." rate to ".$Update_Rate."<br>";
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}

/////////////////////Update GBP/////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.cosmeparadise.com/customprogram/paypal/currencyexchangerate.php?FromCurrency=AUD&ToCurrency=GBP");
$Update_Rate = curl_exec($ch);
curl_close($ch);

$currency_to = "GBP";
$Update_SQL = "UPDATE directory_currency_rate SET rate = ? WHERE currency_from = ? AND currency_to = ?";
try{
	$connection_write->query($Update_SQL, array($Update_Rate, $currency_from, $currency_to));
	echo "finished update ".$currency_to." rate to ".$Update_Rate."<br>";
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}


/////////////////////Update USD/////////////////
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, "http://www.cosmeparadise.com/customprogram/paypal/currencyexchangerate.php?FromCurrency=AUD&ToCurrency=USD");
$Update_Rate = curl_exec($ch);
curl_close($ch);

$currency_to = "USD";
$Update_SQL = "UPDATE directory_currency_rate SET rate = ? WHERE currency_from = ? AND currency_to = ?";
try{
	$connection_write->query($Update_SQL, array($Update_Rate, $currency_from, $currency_to));
	echo "finished update ".$currency_to." rate to ".$Update_Rate."<br>";
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}


?>