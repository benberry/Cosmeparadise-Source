<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/dev.cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
$app = Mage::app();
// Get default store code
$default_store = Mage::app()->getStore();
$default_store_code = $default_store->getCode();
$store = $default_store_code;

Mage::app($store);
//////////// Make connection to call SQL write //////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

$IP = trim($_GET['IP']);
$state = trim($_GET['state']);
$city = trim($_GET['city']);
//Mage::log("orderid:".$orderid, null, "berry.log",true);
//Mage::log("Security_Check:".$Security_Check, null, "berry.log",true);
if($IP != "" && strlen($IP) > 5)
{	$sql = "INSERT INTO city_postcode_AU_IP VALUES (?, ?, ?)";
	try{
		$connection_write->query($sql, array($IP, $state, $city));
	}catch(Exception $e)
	{	Mage::log("IP_state_city.php.php error:".$e->getMessage(), null, "berry.log",true);
	}
}
?>