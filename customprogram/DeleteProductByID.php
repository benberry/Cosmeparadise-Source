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
if(($_POST["single_pid"]) != ""){
		if(($_POST["single_pid"]) == "")
		{	echo "missing order number.";
			exit;		
		}
		
		$single_pid = trim($_POST["single_pid"]);		
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
<h2>Product Deleter(Please do it carefully!!!)</h2>
<b>For CSV,Please keep the format: Product Id <br> (The first row will be skip)</b>
<form id="usrform" action="DeleteProductByID.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> <br/><br />
(Below is for single input. If there is any text input, it will skip the uploaded file!)
<br />
Product ID:<input type="text" name="single_pid" size="20"/>  
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

//////////////////////////////////////
Mage::register("isSecureArea", 1);
///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write


 if (($handle = fopen($csvfile, "r")) !== FALSE || $directinput == true) {	//OPEN CSV
	$product_array = array();
	if($directinput == true)
	{	$product_id = $single_pid; //use your own product id
		
		if(count_pid($product_id, $connection_read) > 0)
		{
			try{
				Mage::getModel("catalog/product")->load( $product_id  )->delete();
				echo "product ID:".$product_id." has been deleted!<br>";
			}catch(Exception $e){
				echo "Product ID:".$product_id." Delete failed! <br>";
			}
		}
		else
			echo "product ID:".$product_id." NOT EXIST!<br>";
		
	}
	else
	{	//echo "CSV";
		$row=1;
		while (($data = fgetcsv($handle)) !== FALSE) {	//go through data	
			$product_id = "";
			if($row > 1)
			{$product_id = trim($data[0]);
				if(count_pid($product_id, $connection_read) > 0)
				{
					try{
						Mage::getModel("catalog/product")->load( $product_id  )->delete();
						echo "product ID:".$product_id." has been deleted!<br>";
					}catch(Exception $e){
						echo "Product ID:".$product_id." Delete failed! <br>";
					}
				}
				else
					echo "product ID:".$product_id." NOT EXIST!<br>";
			}
			$row++;
		}		
	}	
 }
 
 
	function count_pid($product_id, $connection_read){
		$return_sku_count = null;
		$sql = "SELECT COUNT(sku) FROM catalog_product_entity WHERE entity_id =".$product_id;
		//foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_order) {			}
		$return_sku_count = $connection_read->fetchOne($sql);
		if($return_sku_count != "" && $return_sku_count != null)
			return $return_sku_count;
		else
			return 0;
	}
 

?>