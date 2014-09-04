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
if ($ip == "59.148.228.226" ) $accesslist=true;	//company IP

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
/////////////// call SQL read////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		
/////////////// call SQL write///////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

///////////////get MSRP attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'msrp' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$msrp_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////
echo "start<br>";
$products = Mage::getModel('catalog/product')->getCollection();
	//$products->addAttributeToFilter('status', 1);//enabled
	//$products->addAttributeToFilter('visibility', 4);//catalog, search
	//$products->addAttributeToFilter('type_id', 'simple');//catalog, search
	$products->addAttributeToSelect('*');
	$products->addStoreFilter($storeId);
	$prodIds = $products->getAllIds();
	$count = 0;
	foreach($prodIds as $productId) {
		//if($count > 15500 && $count < 20000)
			$count = $count + cNuMSRP($productId, $msrp_attribute, $connection_read, $connection_write);	
		//else if($count > 20000)
		//	break;
		//$count++;
		
	}
	echo "update products:".$count;
////////////////////////////// function ////////////////////////	
function cNuMSRP($product_id, $msrp_attribute,$connection_read,$connection_write) {
	
	$product = Mage::getModel('catalog/product');	
	$product->load($product_id);
	$prod_name = $product->getName();
	$MSRP = $product->getMsrp();	
	$price = $product->getPrice();
	$newprice = $price * 1.2;	
	if(_checkIfMSRPExists($product_id, $connection_read) == false)
	{	_insertMSRP($newprice, $product_id, $msrp_attribute, $connection_write);
		return 1;
	}
		
	if($MSRP <= $price || $MSRP == null)	
	{	//echo "product ID:".$product_id." missing MSRP or MSRP not correct. Price:".$price." Will update to:".$newprice."<br>";
		_updateMSRP($price * 1.2, $product_id, $msrp_attribute, $connection_write);	
		return 1;
	}
	else
		return 0;
		//echo "product ID:".$product_id." have correct MSRP<br>";
}	

function _checkIfMSRPExists($product_id, $connection_read){   
    $sql   = "SELECT COUNT(*) FROM catalog_product_entity_decimal WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'msrp' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')) AND entity_id = ?";
    $count = $connection_read->fetchOne($sql, array($product_id));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}

function _updateMSRP($newPrice, $product_id, $attributeId, $connection_write){   
    $sql = "UPDATE catalog_product_entity_decimal cped
            SET  cped.value = ?
            WHERE  cped.attribute_id = ?
            AND cped.entity_id = ?";
    $connection_write->query($sql, array($newPrice, $attributeId, $product_id));
}

function _insertMSRP($newPrice, $product_id, $attributeId, $connection_write){   
    $sql = "INSERT INTO catalog_product_entity_decimal (entity_type_id, attribute_id, store_id, entity_id, value)
			VALUES (?, ?, ?, ?, ?)";
    $connection_write->query($sql, array(4, $attributeId, 0, $product_id, $newPrice));
}