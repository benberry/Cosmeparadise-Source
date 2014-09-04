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
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

///////////////get price attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'price' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$price_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get MSRP attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'msrp' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$msrp_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get status attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'status' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$status_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get sbn_pid attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'sbn_pid' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$sbn_pid_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get current_product_type attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'current_product_type' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$current_product_type_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get currency rate AUD TO HKD///////////////
$sql = "SELECT rate FROM directory_currency_rate WHERE currency_from = 'AUD' AND currency_to = 'HKD';";
$currency_rate = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

$icount	= 0;
$ucount	= 0;

////////////////////////download SBN full product feed////////////////////////
try{
download_remote_file_with_curl('http://affiliate.strawberrynet.com/affiliate/cgi/directListXML.aspx?siteID=cosmeparadise&langID=1&currency=HKD', realpath("./") . '/SBNallproducts.xml');
echo "finished download file, will open it now. <br>";
}
catch(Exception $e)
{	echo 'Caught exception: ', $e->getMessage(), "\n";
}

$xml = simplexml_load_file("SBNallproducts.xml") 
   or die("Error: Cannot create object");

/////////////////CLEAR ALL FROM SBN_products////////////////////////
$sql = "TRUNCATE TABLE SBN_products";
$connection_write->query($sql);
/////////////////////////import into our database/////////////////
$Insert_sql = "INSERT INTO SBN_products (ProdId, SellingPrice, RefPrice, InvQty, SBN_Brand, SBN_Category) VALUES (?, ?, ?, ?, ?, ?)";
foreach($xml->children() as $Item){
	
	try{
		$connection_write->query($Insert_sql, array($Item->ProdId, str_replace(",","",$Item->SellingPrice), str_replace(",","",$Item->RefPrice), $Item->InvQty, $Item->ProdBrandLangName, $Item->ProdCatgName));
	 }catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	
	$icount++;
}
echo "finish import into database<br>";
//LEFT JOIN catalog_category_product ccp ON ccp.product_id = cpev.entity_id AND ccp.category_id IN (4,38,39,40,42,190,243) -- fragrance category
///////////////////////////get data from database to update//////////////////
$GetItemSQL = "SELECT SBN_products.* , cpev.entity_id AS product_id, cpev.value AS SBN_id, cpei.value AS TOSBN
FROM catalog_product_entity_varchar cpev
LEFT JOIN catalog_product_entity_int cpei ON cpei.entity_id = cpev.entity_id AND cpei.attribute_id = ".$current_product_type_attribute." AND cpei.value = (SELECT option_id FROM eav_attribute_option_value WHERE value = 'SBN' AND store_id = 0)
LEFT JOIN SBN_products ON cpev.value = SBN_products.ProdId
WHERE cpev.attribute_id =".$sbn_pid_attribute;
foreach ($connection_read->fetchAll($GetItemSQL) as $SBN_product) {

	if($SBN_product['TOSBN'] != null && $SBN_product['TOSBN'] != "")
	{if($SBN_product['ProdId'] == null || $SBN_product['InvQty'] <= 0)
		_updateStatus($SBN_product['product_id'], $status_attribute, "2", $connection_write);
	else
	{	_updateStatus($SBN_product['product_id'], $status_attribute, "1", $connection_write);
		//echo "SBNid:".$SBN_product['ProdId']."--SBN_Brand:".$SBN_product['SBN_Brand']."--SBN_Category:".$SBN_product['SBN_Category']."<br>";
		if(strpos($SBN_product['SBN_Category'], "Fragrance") !== false)	////////check if fragrance items
		{	$sellingprice = $SBN_product['SellingPrice'] * 0.988;
			//echo "fragrance, category_id:".$SBN_product['fra_cate_id']."<br>";
		}
		else
		{	//$sellingprice = $SBN_product['SellingPrice'] * 0.96;
			$Catprice = _category_to_price($SBN_product['SBN_Category'], $SBN_product['SellingPrice']);
			$Brandprice = _brand_to_price($SBN_product['SBN_Brand'], $SBN_product['SellingPrice']);
			if($Catprice != $SBN_product['SellingPrice'] && $Brandprice != $SBN_product['SellingPrice'])
			{	if($Catprice >= $Brandprice)
					$sellingprice = $Catprice;
				else
					$sellingprice = $Brandprice;			
			}
			else if($Catprice != $SBN_product['SellingPrice'] && $Brandprice == $SBN_product['SellingPrice'])
				$sellingprice = $Catprice;
			else if($Catprice == $SBN_product['SellingPrice'] && $Brandprice != $SBN_product['SellingPrice'])
				$sellingprice = $Brandprice;
			else
				$sellingprice = $SBN_product['SellingPrice'] * 0.988;
			//echo "Non fragrance, category_id:".$SBN_product['fra_cate_id']." pid:".$SBN_product['product_id']."<br>";
		}
		//////////////update for currency rate HKD => AUD//////////////
		$SBN_product_price = $sellingprice/$currency_rate;		
		/////Avoid  free gift under cost 20140429///////////////////
		if($SBN_product_price < 25)
			$SBN_product_price = $SBN_product_price+1;
		else if($SBN_product_price < 30)
			$SBN_product_price = $SBN_product_price+3;
		else if($SBN_product_price < 60)
			$SBN_product_price = $SBN_product_price+2;
		else if($SBN_product_price < 90)
			$SBN_product_price = $SBN_product_price+1.2;
		else
			$SBN_product_price = $SBN_product_price+0.8;
		
		$SBN_product_price = round($SBN_product_price,2);
		_updatePrices($SBN_product_price, $SBN_product['product_id'], $price_attribute, $connection_write);
		
		////////////////handle MSRP//////////////////
		$RefPrice = $SBN_product['RefPrice']/$currency_rate;
		$RefPrice = round($RefPrice,2);
		if($SBN_product['RefPrice'] == 0 || $RefPrice <= $SBN_product_price)
			$RefPrice = $SBN_product_price * 1.2;
					
		if(_checkIfMSRPExists($SBN_product['product_id'], $connection_read) == true)
		{	_updateMSRP($RefPrice, $SBN_product['product_id'], $msrp_attribute, $connection_write);
			//echo $SBN_product['product_id']." MSRP EXIST, new MSRP is:".$SBN_product['RefPrice']."<br>";
		}
		else
		{	_insertMSRP($RefPrice, $SBN_product['product_id'], $msrp_attribute, $connection_write);
			//echo $SBN_product['product_id']." MSRP not EXIST, new MSRP is:".$SBN_product['RefPrice']."<br>";
		}
		//echo "product_id:".$SBN_product['product_id']." SKU: S".$SBN_product['ProdId']." price updated to AUD".$SBN_product['SellingPrice']."--<br>";
		//$price_updated .= "product_id:".$SBN_product['product_id']." SKU: S".$SBN_product['ProdId']." price updated 	to AUD".$SBN_product['SellingPrice']."\r\n";
	}
	}
	flush();
}

/////////////////do product price reindex in Magento//////////////////
$process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');
$process->reindexAll();

$sCharset = 'utf-8';
	 
	 // To	 	 
	 //$sMailTo = 'union.programmer@gmail.com';
	 $sMailTo = 'report@cosmeparadise.com, support@cosmeparadise.com';
	//$mail->Username = 'report@cosmeparadise.com';                            // SMTP username
	//	$mail->Password = 'FecesLevelShyestHoused91';                           // SMTP password
	 //$sMailFrom = 'Cosme Paradise - Marketing <marketing@cosmeparadise.com>';
	 $sMailFrom = 'Cosme Paradise <support@cosmeparadise.com>';
	 // subject
	 $sSubject = "SBN price update programme";
	 // contents
	 //$sMessage = $price_updated.$sku_miss;
	 $sMessage = "SBN product price updated.";
	 
	 /////////////////////SEND EMAIL///////////////////////
		 $sHeaders = "MIME-Version: 1.0\r\n" .
	 			"Content-type: text/html; charset=$sCharset\r\n" .
	 			"From: $sMailFrom\r\n";
	 
	 //send email
	 $mail_sent = @mail($sMailTo, $sSubject, $sMessage, $sHeaders);
	//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
	 echo $mail_sent ? "Mail sent!" : "Mail failed";
	 

///////////////////////function///////////////////////
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

function _checkIfSkuExists($sku, $connection_read){   
    $sql   = "SELECT COUNT(*) AS count_no FROM catalog_product_entity WHERE sku = ?";
    $count = $connection_read->fetchOne($sql, array($sku));
    if($count > 0){
        return true;
    }else{
        return false;
    }
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

function _getIdFromSku($sku, $connection_read){    
    $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku = ?";
    return $connection_read->fetchOne($sql, array($sku)); 
}

function _updatePrices($newPrice, $product_id, $attributeId, $connection_write){   
    $sql = "UPDATE catalog_product_entity_decimal cped
            SET  cped.value = ?
            WHERE  cped.attribute_id = ?
            AND cped.entity_id = ?";
    $connection_write->query($sql, array($newPrice, $attributeId, $product_id));
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

function _updateStatus($product_id, $status_attribute, $status, $connection_write){   
	//1:Enable 2:Disable
    $sql = "UPDATE catalog_product_entity_int cpei
                SET  cpei.value = ?
            WHERE  cpei.attribute_id = ?
            AND cpei.entity_id = ?";
    $connection_write->query($sql, array($status, $status_attribute, $product_id));
}

function _category_to_price($Category, $Origin_price)
{//update rule Nov 5
$sellingprice = $Origin_price;
if($Category == "Hair Care") $sellingprice = $Origin_price * 0.96;
else if($Category == "Make Up") $sellingprice = $Origin_price * 0.96;
else if($Category == "Men's Fragrance") $sellingprice = $Origin_price * 0.988;
else if($Category == "Ladies Fragrance") $sellingprice = $Origin_price * 0.988;
else if($Category == "Men's Skincare") $sellingprice = $Origin_price * 0.96;
else if($Category == "Skincare") $sellingprice = $Origin_price * 0.97;

return $sellingprice;
}

function _brand_to_price($Brand, $Origin_price)
{//update rule Nov 5
$sellingprice = $Origin_price;
if($Brand == "Academie") $sellingprice = $Origin_price * 0.98;
else if($Brand == "Acca Kappa") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Aesop") $sellingprice = $Origin_price * 0.963;
else if($Brand == "American Crew") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Anna Sui") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Annayake") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Anthony") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Aveda") $sellingprice = $Origin_price * 0.957;
else if($Brand == "Bare Escentuals") $sellingprice = $Origin_price * 0.957;
else if($Brand == "Becca") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Benefit") $sellingprice = $Origin_price * 0.95;
else if($Brand == "Bioderma") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Biotherm") $sellingprice = $Origin_price * 0.86;
else if($Brand == "Bobbi Brown") $sellingprice = $Origin_price * 0.95;
else if($Brand == "Borghese") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Bourjois") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Burberry") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Calvin Klein") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Chanel") $sellingprice = $Origin_price * 0.972;
else if($Brand == "Chantecaille") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Christian Dior") $sellingprice = $Origin_price * 0.963;
else if($Brand == "Clarins") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Cle De Peau") $sellingprice = $Origin_price * 0.975;
else if($Brand == "Clinique") $sellingprice = $Origin_price * 0.95;
else if($Brand == "Cowshed") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Darphin") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Decleor") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Dermalogica") $sellingprice = $Origin_price * 0.965;
else if($Brand == "Dolce & Gabbana") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Elemis") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Elizabeth Arden") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Ella Bache") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Eminence") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Estee Lauder") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Gatineau") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Giorgio Armani") $sellingprice = $Origin_price * 0.955;
else if($Brand == "H2O+") $sellingprice = $Origin_price * 0.95;
else if($Brand == "Helena Rubinstein") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Hugo Boss") $sellingprice = $Origin_price * 0.99;
else if($Brand == "Jurlique") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Kiehl's") $sellingprice = $Origin_price * 0.97;
else if($Brand == "La Mer") $sellingprice = $Origin_price * 0.92;
else if($Brand == "La Prairie") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Lancome") $sellingprice = $Origin_price * 0.955;
else if($Brand == "L'Occitane") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Make Up For Ever") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Max Factor") $sellingprice = $Origin_price * 0.93;
else if($Brand == "MD Skincare") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Missha") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Murad") $sellingprice = $Origin_price * 0.955;
else if($Brand == "NARS") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Olay") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Origins") $sellingprice = $Origin_price * 0.97;
else if($Brand == "Paul Mitchell") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Payot") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Pupa") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Redken") $sellingprice = $Origin_price * 0.94;
else if($Brand == "RMK") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Schwarzkopf") $sellingprice = $Origin_price * 0.95;
else if($Brand == "Shiseido") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Shu Uemura") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Sisley") $sellingprice = $Origin_price * 0.96;
else if($Brand == "SK II") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Stendhal") $sellingprice = $Origin_price * 0.955;
else if($Brand == "Stila") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Sulwhasoo") $sellingprice = $Origin_price * 0.96;
else if($Brand == "T. LeClerc") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Valmont") $sellingprice = $Origin_price * 0.96;
else if($Brand == "Youngblood") $sellingprice = $Origin_price * 0.94;
else if($Brand == "Yves Saint Laurent") $sellingprice = $Origin_price * 0.96;


return $sellingprice;
}

//////////////////////////////////////////////////////////
?>