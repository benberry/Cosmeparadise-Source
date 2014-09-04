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
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
/*echo "start <br>";
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/dev.cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
//$app = Mage::app();
Mage::app('admin');*/
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

$price_updated = "";
$sku_miss = "";
$emailcontent = "";
Mage::app($store);
///////////////////////////////////////////////////////////////
//// call SQL read
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		
//// call SQL write
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

///////////////get product_line attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'product_line' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$product_line_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

$sql = "SELECT option_id FROM eav_attribute_option WHERE attribute_id = ".$product_line_attribute;
foreach ($connection_read->fetchAll($sql) as $All_Line_Option) {
	$product_id_list = array();
	$getProductSql= "SELECT entity_id FROM catalog_product_entity_int cpei WHERE cpei.attribute_id = ".$product_line_attribute." AND cpei.value = ".$All_Line_Option['option_id']." AND store_id = 0";
	foreach ($connection_read->fetchAll($getProductSql) as $getproduct) {
		array_push($product_id_list, $getproduct['entity_id']);	
	}
	if(count($product_id_list)> 1)
		doUpSell($product_id_list, count($product_id_list));
}
echo "finish <br>";

function doUpSell($product_id_list, $total)
{	print_r($product_id_list);
	foreach($product_id_list AS $Product_ID)
	{	echo "<br>".$Product_ID."<br>";
		$upsell_list = array();
		$_product = Mage::getModel('catalog/product')->load($Product_ID);
		
		if($total >= 5)
		{$get_random_upsell_list = array_rand($product_id_list, 5);
		 //print_r($get_random_upsell_list);
			for($i=0; $i<5; $i++)
			{	
				$random_product_id = $product_id_list[$get_random_upsell_list[$i]];
				if($Product_ID != $random_product_id)
				{	$position = array('position'=>$i);
					$upsell_list[$random_product_id] = $position;
				}
			}
		print_r($upsell_list);		
		$param = $upsell_list;		
		 //for up-sells
		$_product->setUpSellLinkData($param);
		//for crosssells
		$_product->setCrossSellLinkData($param);
		 $_product->save();
		}
		else if($total > 1)
		{	$temp_count = 0;
			foreach($product_id_list AS $sub_id)
			{	
				if($Product_ID != $sub_id)
				{	$position = array('position'=>$temp_count);
					$upsell_list[$sub_id] = $position;
					$temp_count++;
				}
			}
		//print_r($upsell_list);		
		$param = $upsell_list;		
		 //for up-sells
		$_product->setUpSellLinkData($param);		
		 $_product->save();
		}	
		
		/*$param = array(
				38891=>array('position'=>0),
				38867=>array('position'=>1),
				38887=>array('position'=>2),
		);
		//for related
		$_product->setRelatedLinkData($param);
		//for up-sells
		$_product->setUpSellLinkData($param);
		//for crosssells
		$_product->setCrossSellLinkData($param);
		*/
		
	}
}

?>




				