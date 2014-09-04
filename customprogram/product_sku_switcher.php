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
if ($ip == "59.148.228.226" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
$directinput = false;
if(($_POST["single_sku"]) != ""){
		if(($_POST["single_sku"]) == "")
		{	echo "missing order number.";
			exit;		
		}
		
		$single_sku = trim($_POST["single_sku"]);		
		$directinput = true;
}

if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
	|| ($_FILES["file"]["type"] == "text/comma-separated-values"))
	{
	if ($_FILES["file"]["error"] > 0)
		{   echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; exit;    }
	}
  } 
else
  {
?>

<html>
<body>
<h2>Product sku switcher</h2>
<b>For CSV,Please keep the format: sku (current sku)<br> (The first row will be skip)</b>
<form id="usrform" action="product_sku_switcher.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> <br/><br />
(Below is for single input. If there is any text input, it will skip the uploaded file!)
<br />
sku:<input type="text" name="single_sku" size="20"/>  
<br>
Product type:<select name="product_type">
  <option value="Cosmeparadise" SELECTED >Cosmeparadise</option>
  <option value="BONJOUR">BONJOUR</option>
  <option value="SBN">SBN</option>
</select>
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
  exit;
  }
if($directinput == false)
{	$csvfile = $_FILES["file"]["tmp_name"];
	
	if(!file_exists($csvfile)) {
		echo "File not found.";
		exit;
	}
	
	$size = filesize($csvfile);
	if(!$size) {
		echo "File is empty.\n";
		exit;
	}
}
?>

<?php
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

$product_type = $_POST['product_type'];
if($product_type == "Cosmeparadise")
	$product_type_attribure = "union_sku";
else if($product_type == "BONJOUR")
	$product_type_attribure = "bonjour_pid";
else if($product_type == "SBN")
	$product_type_attribure = "sbn_pid";

///////////////get product_type attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = '".$product_type_attribure."' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$product_type_attribute_id = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get current_product_type attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'current_product_type' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$current_product_type_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

 if (($handle = fopen($csvfile, "r")) !== FALSE || $directinput == true) {	//OPEN CSV
	$product_array = array();
	if($directinput == true)
	{	$sku = $single_sku;
		
		$product_id = check_sku_pid($sku, $connection_read);
		$new_sku = get_new_sku($product_id, $product_type_attribute_id, $connection_read);
		if(count_sku($new_sku, $connection_read) >= 1)
		{
			echo "sku:".$sku." to new sku:".$new_sku."is duplicated. Please check before update!<br>";
		}
		else if($product_id != 0)
		{	
			if($new_sku != "")
			{	try{
				update_sku($sku, $new_sku, $product_type_attribure, $connection_write);
				update_current_product_type($product_type, $product_id, $current_product_type_attribute, $connection_write);
				}catch(Exception $e)
				{	echo 'Caught exception: ', $e->getMessage(), "\n";
				}
			}
			else
				echo "sku:".$sku." can't find its new sku!<br>";
		}else
			echo "sku:".$sku." is not exist.<br>";		
	}
	else
	{	//echo "CSV";
		$row=1;
		while (($data = fgetcsv($handle)) !== FALSE) {	//go through data	
			$sku = "";
			if($row > 1)
			{$sku = trim($data[0]);
			 $product_id = check_sku_pid($sku, $connection_read);
			 $new_sku = get_new_sku($product_id, $product_type_attribute_id, $connection_read);
				if(count_sku($new_sku, $connection_read) >= 1)
				{
					echo "sku:".$sku." to new sku:".$new_sku."is duplicated. Please check before update!<br>";
				}
				else if($product_id != 0)
				{	
					if($new_sku != "")
					{	try{
							update_sku($sku, $new_sku, $product_type_attribure, $connection_write);
							update_current_product_type($product_type, $product_id, $current_product_type_attribute, $connection_write);
						}catch(Exception $e)
						{	echo 'Caught exception: ', $e->getMessage(), "\n";
						}
					}
					else
						echo "sku:".$sku." can't find its new sku!<br>";
				}else
					echo "sku:".$sku." is not exist.<br>";		
			}
			$row++;
		}		
	}	
 }
 
 
	function count_sku($sku, $connection_read){
		$return_sku_count = null;
		$sql = "SELECT COUNT(sku) FROM catalog_product_entity WHERE sku ='".$sku."' GROUP BY sku";
		//foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {			}
		$return_sku_count = $connection_read->fetchOne($sql);
		if($return_sku_count != "" && $return_sku_count != null)
			return $return_sku_count;
		else
			return 0;
	}
 
	function check_sku_pid($sku, $connection_read){		
		$return_sku_pid = null;
		$sql = "SELECT entity_id FROM catalog_product_entity WHERE sku ='".$sku."'";
		//foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {			}
		$return_sku_pid = $connection_read->fetchOne($sql);
		if($return_sku_pid != "" && $return_sku_pid != null)
			return $return_sku_pid;
		else
			return 0;
	}
	
	function get_new_sku($product_id, $product_type_attribute_id, $connection_read){
		$return_new_sku = null;
		$sql = "SELECT value FROM catalog_product_entity_varchar WHERE entity_id ='".$product_id.".' AND attribute_id = ".$product_type_attribute_id;		
		$return_new_sku = $connection_read->fetchOne($sql);
		if($return_new_sku != "" && $return_new_sku != null)
			return $return_new_sku;
		else
			return "";
	}

	function update_sku($sku, $new_sku, $product_type_attribure, $connection_write){
		if($product_type_attribure == "sbn_pid")
			$new_sku = "S".$new_sku;
		$update_sql = "UPDATE catalog_product_entity SET sku=? WHERE sku = ?";
		$connection_write->query($update_sql, array($new_sku, $sku));
		echo "origin sku:".$sku." has been updated to new sku:".$new_sku;
	}

	function update_current_product_type($product_type, $product_id, $current_product_type_attribute, $connection_write){
		$update_sql = "UPDATE catalog_product_entity_int SET value=(SELECT option_id FROM eav_attribute_option_value WHERE value = ? AND store_id = 0) WHERE entity_id = ? AND attribute_id = ?";
		$connection_write->query($update_sql, array($product_type, $product_id, $current_product_type_attribute));
		echo "--- And current product type been updated to ".$product_type."!<br>";
	}

?>