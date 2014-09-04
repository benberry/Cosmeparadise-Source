<?php
	if($_POST['datafeed'] != "")	
	{	echo "Will get the download file...";		
	}
else
{
?>

<html>
<body>
<h2>Disabled product in datafeed Manager</h2>
<form action="disabled_datafeed_product.php" method="post" enctype="multipart/form-data">
<br>
Datefeed:<select name="datafeed">
  <option value="getprice" SELECTED >getprice</option>
  <option value="nextag">nextag</option>
  <option value="googleshopping">googleshopping</option>
  <option value="shoppingdotcom">shoppingdotcom</option>
</select>
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php
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
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read


///////////////get datafeed attribute ID///////////////
if($_POST['datafeed'] == 'getprice')
	$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'datafeed_getprice' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
else if($_POST['datafeed'] == 'nextag')
	$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'datafeed_nextag' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
else if($_POST['datafeed'] == 'googleshopping')
	$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'datafeed_googleshopping' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
else if($_POST['datafeed'] == 'shoppingdotcom')
	$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'datafeed_shoppingdotcom' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";

$datafeed_attribute_id = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////
echo "<br> Here is your selection".$_POST['datafeed']." datafeed attribute id is ".$datafeed_attribute_id."<br>";


///////////////////////////get data from database//////////////////
$GetItemSQL = "
SELECT cpe.sku, cpev.value AS product_name, cpei.value
FROM catalog_product_entity_int cpei
INNER JOIN catalog_product_entity cpe ON cpe.entity_id = cpei.entity_id
INNER JOIN catalog_product_entity_varchar cpev ON cpev.entity_id = cpei.entity_id
AND cpev.attribute_id =71
WHERE cpei.attribute_id =".$datafeed_attribute_id."
AND cpei.value =1";

$line = "";
foreach ($connection_read->fetchAll($GetItemSQL) as $disabled_product) {
$line .= $disabled_product['sku']."\t".$disabled_product['product_name']."\r\n";

}

/////////////open file and write into it///////////////
	$filename = "./disable_datefeed_product.txt";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo "<br><br>success. check file link <a href=\"./disable_datefeed_product.txt\">Here</a><br>";


?>



