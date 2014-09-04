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

$price_updated = "";
$sku_miss = "";
$emailcontent = "";
Mage::app($store);
///////////////////////////////////////////////////////////////
/*
Process Name			ID			Code
Product Attributes		1			catalog_product_attribute
Product Prices			2			catalog_product_price
Catalog URL Rewrites	3			catalog_url
Product Flat Data		4			catalog_product_flat
Category Flat Data		5			catalog_category_flat
Category Products		6			catalog_category_product
Catalog Search Index	7			catalogsearch_fulltext
Stock Status			8			cataloginventory_stock
Tag Aggregation Data	9			tag_summary
Brand Category Index	10			product
Brand Url Index			11			url

$process = Mage::getModel('index/process')->load(2);
$process->reindexAll();
or
$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
$process->reindexAll();
*/
/////////////////do product price reindex in Magento//////////////////
try{
for ($i = 1; $i <= 9; $i++) {
    $process = Mage::getModel('index/process')->load($i);
    $process->reindexAll();
}
echo "finished reindex";
}catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>