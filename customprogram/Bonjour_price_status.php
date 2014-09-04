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
////////////////////timer//////////////
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 

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

Mage::app();
///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

///////////////get price attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'price' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$price_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get status attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'status' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$status_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get bonjour_pid attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'bonjour_pid' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
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

// Get a file into an array.  Get the HTML source of a URL.
$lines = file('http://www.bonjourhk.com/bot/DataFeed3.aspx?type=product&user=wholesales&key=CCCCCC');

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($lines as $line_num => $line) {
	//if($line_num == 0)
	//{
	//	$tab_value = explode("\t", $line);
	//	echo "Value 0:".$tab_value[0]."\t";
	//	echo "Value 4:".$tab_value[4]."\t";
	//	echo "Value 6:".$tab_value[6]."\t<br>";
	//}
	if($line_num > 0)
	{	$tab_value = explode("\t", $line);
		$sku = $tab_value[0];
		$wholesale_price = $tab_value[4];
		$stock = $tab_value[6];
		if(_checkIfSkuExists($sku, $connection_read))
		{	//echo "sku:".$sku." exist in line ".$line_num."<br>";
			$PID = _getIdFromSku($sku, $connection_read);
			//echo "PID:".$PID." exist in line ".$line_num."<br>";
			if(_IfBonjour($PID, $connection_read))	//////check if current product type in bonjour
			{	//////update status////////
				if($stock <= 0)
					_updateStatus($PID, $status_attribute, "2", $connection_write);
				else
					_updateStatus($PID, $status_attribute, "1", $connection_write);
			}	
		}
	}
   // echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
	//if($line_num > 10)
	//	break;
}

	/////////////////get execution time//////////////
	$mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $endtime = $mtime; 
   $totaltime = ($endtime - $starttime); 
   $eMailContent = "Bonjour update took ".$totaltime." seconds  to finish.<br>"; 
   echo $eMailContent;
   
   sendEmail($eMailContent);
//////////////////////////////////function////////////////////////////////////////////
function _checkIfSkuExists($sku, $connection_read){ 
    $sql   = "SELECT COUNT(*) AS count_no FROM catalog_product_entity WHERE sku = ?";
    $count = $connection_read->fetchOne($sql, array($sku));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}
function _IfBonjour($PID, $connection_read){   
    $sql   = "SELECT entity_id FROM catalog_product_entity_int WHERE attribute_id = 150 AND (value = 11954 OR value = 11955 OR value = 1232) AND entity_id = ?";	//live AU
    //$sql   = "SELECT entity_id FROM catalog_product_entity_int WHERE attribute_id = 150 AND (value = 11963 OR value = 11962 OR value = 1232) AND entity_id = ?";	//live US
    //$sql   = "SELECT entity_id FROM catalog_product_entity_int WHERE attribute_id = 150 AND (value = 42992 OR value = 1233) AND entity_id = ?";	//dev
    $count = $connection_read->fetchOne($sql, array($PID));
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

function _updateStatus($product_id, $status_attribute, $status, $connection_write){   
	//1:Enable 2:Disable
    $sql = "UPDATE catalog_product_entity_int cpei
                SET  cpei.value = ?
            WHERE  cpei.attribute_id = ?
            AND cpei.entity_id = ?";
    $connection_write->query($sql, array($status, $status_attribute, $product_id));
}   

function sendEmail($eMailContent)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";
		require_once $path_include;		
		$mail = new PHPMailer;		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'sipau2-01.nexcess.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'report@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'FecesLevelShyestHoused91';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'report@cosmeparadise.com';
		$mail->FromName = 'Bonjour update Notice';
		$mail->AddAddress('support@cosmeparadise.com', 'Berry Lai');  // Add a recipient
		//$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters		
		$mail->IsHTML(true);                                  // Set email format to HTML		
		$mail->Subject = 'Bonjour status updated';
		$mail->Body    = $eMailContent;		
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo ' Message has been sent <br>';		 
	 } 
?>