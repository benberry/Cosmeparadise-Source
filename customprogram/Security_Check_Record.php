<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
$app = Mage::app();
// Get default store code
$default_store = Mage::app()->getStore();
$default_store_code = $default_store->getCode();
$store = $default_store_code;

Mage::app($store);
//////////// Make connection to call SQL write //////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

$orderid = $_GET['orderid'];
$Security_Check = $_GET['Security_Check'];
//Mage::log("orderid:".$orderid, null, "berry.log",true);
//Mage::log("Security_Check:".$Security_Check, null, "berry.log",true);

$sql = "UPDATE sales_flat_order SET securitycheck = ? WHERE entity_id = ?";
	try{
		$connection_write->query($sql, array($Security_Check, $orderid));
	}catch(Exception $e)
	{	Mage::log("Security_Check_Record.php error:".$e->getMessage(), null, "berry.log",true);
	}
?>