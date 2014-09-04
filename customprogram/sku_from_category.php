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
$directinput = false;
if(($_POST["cat_id"]) != ""){
	$cat_id = trim($_POST["cat_id"]);		
	$directinput = true;
}
else
  {
?>

<html>
<body>
<h2>Get sku from category</h2>
<form id="usrform" action="sku_from_category.php" method="post" enctype="multipart/form-data">

(Input the Category ID, look into backend!)
<br />
Category ID:<input type="text" name="cat_id" size="20"/>  
<br>
Better open with open office.
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
$emailcontent = "";
Mage::app($store);

///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

$filename="cat".$cat_id.date('Y-m-d H:i:s').".csv";
// Send Header
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel"); 


$delimiter = "\""   ;
$fieldbreak = ","    ;
$linebreak = "\n"      ;
$line = "sku".$linebreak;
///////////////get sku from category ID///////////////
$sql = "SELECT sku FROM catalog_category_product 
INNER JOIN catalog_product_entity ON catalog_product_entity.entity_id = catalog_category_product.product_id
WHERE category_id = ".$cat_id;
///////////////////////////////////////////////////


	if($directinput == true && $cat_id > 0)
	{	try{
			foreach ($connection_read->fetchAll($sql) as $cat_sku) 
			{	$line .= $delimiter.$cat_sku['sku'].$delimiter.$linebreak;
				
			}
		}catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
		
		echo $line;
	}


?>