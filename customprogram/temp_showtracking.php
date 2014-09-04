<?php
echo "start <br>";
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/dev.cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
//$app = Mage::app();
Mage::app('admin');
/////////Order Id/////////
$shippingOrderId=3749;

$tracks = Mage::getModel('sales/order_shipment_track')
    ->getCollection()
    ->addAttributeToSelect('track_number')
    ->setOrderFilter($shippingOrderId);
foreach ($tracks as $track) {
	echo "trackingNumbers:".$track->getTrackNumber()."<br>";
    //$trackingNumbers[] = '<a href="#" onclick="popWin(\'' . $trackingUrl . '\',\'trackorder\',\'width=800,height=600,left=0,top=0,resizable=yes,scrollbars=yes\')" >' . $this->escapeHtml($track->getTrackNumber()) . '</a>';
}

echo "finish <br>";
?>




				