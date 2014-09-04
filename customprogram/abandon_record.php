<?php
///////////////////////////get magento require file/////////////////
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

if (isset($_GET['show_stores']) && ($_GET['show_stores'] == 'on')) {
	$stores = Mage::app()->getStores();
	
	foreach ($stores as $i) {
		print $i->getCode() . "<br />";
	}
	exit;
}
if (isset($_GET['store']) && ($_GET['store'] != "")) {
	$store = $_GET['store'];
}
else {
	$store = $default_store_code;
}

Mage::app($store);

// Specify image location.
$ImageFileLocation = "/customprogram/logo.png";

// Cookie setting, logging, or other functions here
/////////////connect database///////////
//$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//////////// Make connection to call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//////////// Make connection to call SQL write

//user_email=union.programmer@gmail.com	&cart_id=	&type=30M
$user_id = $_GET["user_email"];
$cart_id = $_GET["cart_id"];
$type = $_GET["type"];
 

// insert record
		$sql = "UPDATE Abandon_Email_Record SET OPEN='TRUE', Open_Date=ADDDATE( NOW( ) , INTERVAL 12 HOUR )  WHERE user_email='".$user_id."' AND cart_id=".$cart_id." AND Type='".$type."'";		
		$connection_write->query($sql);

$ext = explode('.',$ImageFileLocation);
//echo $_SERVER['DOCUMENT_ROOT'] . $ImageFileLocation;
header("Content-type: image/" . $ext[count($ext)-1]);
Mage::log("DOCUMENT_ROOT:".$_SERVER['DOCUMENT_ROOT'],null,"berry.log",true);
readfile($_SERVER['DOCUMENT_ROOT'] . $ImageFileLocation);

exit;
?>