<?php
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


echo "start<br>";
echo realpath("./")."<br>";
try{
download_remote_file_with_curl('http://affiliate.strawberrynet.com/affiliate/cgi/directListXML.aspx?siteID=cosmeparadise&langID=1&currency=AUD', realpath("./") . '/SBNallproducts.xml');
}
catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}
echo "finished download file, will open it now.";
ob_flush();
flush();

$icount	= 0;

$xml = simplexml_load_file("SBNallproducts.xml") 
   or die("Error: Cannot create object");

///////////////////////////////////////////////////////////////
//$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

/////////////////CLEAR ALL FROM SBN_products////////////////////////
$sql = "DELETE FROM SBN_products";
$connection_write->query($sql);
echo $sql."<br>";

$Insert_sql = "INSERT INTO SBN_products (ProdId, SellingPrice, RefPrice, InvQty) VALUES (?, ?, ?, ?)";
foreach($xml->children() as $Item){
	
	try{
		$connection_write->query($Insert_sql, array($Item->ProdId, $Item->SellingPrice, $Item->RefPrice, $Item->InvQty));
      echo $Item->ProdId."---".$Item->SellingPrice."---".$Item->RefPrice."---".$Item->InvQty."---"."<br />";
	  ob_flush();
	  flush();
	 }catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	
	$icount++;
	
}

echo "Done";

function download_remote_file_with_curl($file_url, $save_to) 
{ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_POST, 0); 
	curl_setopt($ch,CURLOPT_URL,$file_url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$file_content = curl_exec($ch); 
	curl_close($ch);   
	$downloaded_file = fopen($save_to, 'w'); 
	fwrite($downloaded_file, $file_content); 
	fclose($downloaded_file);   
} 
?>