<?php
echo "start <br>";
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/dev.cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
//$app = Mage::app();
Mage::app('admin');
$shippingOrderId=1615;
$shipment = Mage::getModel('sales/order_shipment')->load($shippingOrderId);
$shipment->delete();  

echo "finish <br>";
?>

