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
	
if($_POST['order_number'] == "" ||  $_POST['SBN_productID'] == "")	
{
?>
<html>
<body>
<h2>Create SBN order manually for website</h2>
<form action="SBN_order_API_direct.php" method="post" enctype="multipart/form-data">
Website order number: <input name="order_number" type="text" /> <br><br>
SBN product ID:(separate by comma , and no space, like:12345,124152)<br>
<textarea rows="4" cols="50" name="SBN_productID"></textarea> 
<br />
Run Type:<select name="process">
  <option value="GOAPI" SELECTED >To SBN</option>
  <option value="Check">check first</option>
</select>
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body></html>
<?php
exit;
}
else{
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

$process = $_POST['process'];
$order_number = $_POST['order_number'];
$SBN_productID = $_POST['SBN_productID'];

$price_updated = "";
$emailcontent = "";
Mage::app($store);

////////check if order exist/////////
$order = Mage::getModel('sales/order')->loadByIncrementId($order_number);
if ($order->getId()) {
    echo "Order number exist! Here will run the order API programme.<br>";
}else{
	echo "Order number didn't exist!<br>";
	exit;
}

///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

///////////////get sbn_pid attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'sbn_pid' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$sbn_pid_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

$sql = "SELECT order_to_sbn.entity_id AS order_id, order_to_sbn.increment_id AS order_no, order_to_sbn.created_at,ADDDATE(NOW(), INTERVAL 4 HOUR),TIMEDIFF(ADDDATE(NOW() , INTERVAL 4 HOUR), order_to_sbn.created_at) AS update_diff, order_to_sbn.state, order_to_sbn.shipping_description,
shipping_address.firstname, shipping_address.lastname, shipping_address.company,
shipping_address.street AS address, shipping_address.region AS state, shipping_address.city,
shipping_address.postcode,country.country_name,shipping_address.telephone, shipping_address.fax
FROM sales_flat_order order_to_sbn
INNER JOIN sales_flat_order_address shipping_address ON shipping_address.parent_id = order_to_sbn.entity_id AND shipping_address.address_type = 'shipping'
INNER JOIN geoip_countrylist country ON country.country_code = shipping_address.country_id
WHERE order_to_sbn.increment_id = ".$order_number." 
ORDER BY order_id ASC";
//AND TIMEDIFF(ADDDATE(NOW(), INTERVAL 4 HOUR), order_to_sbn.created_at) <  TIME('94:59:59')
foreach ($connection_read->fetchAll($sql) as $SBN_order) {	///get all paid processing orders within 24 hours
	/*$check_gift_SQL = "SELECT COUNT(item_id) AS gift_qty FROM sales_flat_order_item items WHERE items.order_id =".$SBN_order['order_id']." AND is_virtual IS NULL AND name LIKE '%FREE GIFT%'";
	$gift_qty = $connection_read->fetchOne($check_gift_SQL);
	if( $gift_qty == 0)	///////////check if have free gift, if yes then continue/////////
	*/
		$SBN_product_array = array();
		$ALLSBN = true;
		$IsSBN = false;
		$SBN_shipping = 0;
		$API_reponse = "";
			
		
			if( strpos($SBN_order['shipping_description'], "Express Shipping") !== false)
				$SBN_shipping = 1;						
			//////////////////////limit address length////////////////
				$shiptoaddress = str_replace(array("\r\n", "\r", "\n", "\""), "",$SBN_order['address']);
				$shiptoaddress1 = "";
				$shiptoaddress2 = "";
				$shiptoaddress3 = "";
				if( strlen($shiptoaddress) > 50)
				{ 	$tempstring = substr($shiptoaddress,0,50);
					$shiptoaddress1 = substr($tempstring,0,strrpos($tempstring," "));
					$pos=strlen($shiptoaddress1);
					$temp_shiptoaddress2 = substr($shiptoaddress, $pos, strlen($shiptoaddress));
					//echo "<br>".$pos."<br>";
						if( strlen($temp_shiptoaddress2) > 50)
					{ 	$tempstring2 = substr($temp_shiptoaddress2,0,50);
						$shiptoaddress2 = substr($tempstring2,0,strrpos($tempstring2," "));
						$pos2=strlen($shiptoaddress2);
						$shiptoaddress3 = substr($temp_shiptoaddress2, $pos, strlen($temp_shiptoaddress2));
					}else
						$shiptoaddress2 = $temp_shiptoaddress2;
					$shiptoaddress1 = trim($shiptoaddress1);
					$shiptoaddress2 = trim($shiptoaddress2);
					$shiptoaddress3 = trim($shiptoaddress3);
					//echo $shiptoaddress1."<BR>".$shiptoaddress2."<BR>".$shiptoaddress3."<br><br>";
				}else
					$shiptoaddress1 = $shiptoaddress;
			//////////////////////end limit address length////////////////	
			$telephone=trim(str_replace(" ","",$SBN_order['telephone']));
			//".rawurlencode(trim($SBN_order['fax']))."
				$SBN_url = "http://affiliate.strawberrynet.com/affiliate/CGI/receiveResponse.aspx?siteID=cosmeparadise&firstname=".rawurlencode(trim($SBN_order['firstname']))."&lastname=".rawurlencode(trim($SBN_order['lastname']))."&compname=".rawurlencode(trim($SBN_order['company']))."&addr1=".rawurlencode($shiptoaddress1)."&addr2=".rawurlencode($shiptoaddress2)."&addr3=".rawurlencode($shiptoaddress3)."&state=".rawurlencode($SBN_order['state'])."&city=".rawurlencode(trim($SBN_order['city']))."&postalcode=".rawurlencode(trim($SBN_order['postcode']))."&country=".rawurlencode($SBN_order['country_name'])."&tel=".rawurlencode($telephone)."&fax=&affiliateref=".rawurlencode($SBN_order['order_no'])."&products=".rawurlencode($SBN_productID)."&shipping=".$SBN_shipping;
				if($process == "GOAPI")
				{	$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_productID,$connection_write);
					echo $API_reponse."<br>".$SBN_url;	//urlencode($SBN_url)
				}else
					echo $API_reponse."<br>".$SBN_url;
					//echo "Order Number:".$SBN_order['order_no']." need to do security check.<br>";
		
}
}
///////////////////////////function///////////////////////////
function connect_SBN_API($SBN_url,$order_no,$SBN_productID,$connection_write) 
{ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $SBN_url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	$file_content = curl_exec($ch); 
	curl_close($ch);	
	$xml = simplexml_load_string($file_content);	
	try{
		$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 12 HOUR ))";
		$connection_write->query($Insert_SBN_order, array($order_no, $SBN_productID, "(Manually)".$xml->status)); 	
		sendEmail($file_content, $order_no, "(Manually)".$xml->status);
	 }catch(Exception $e)
		{	echo 'Caught exception: ', $e->getMessage(), "\n";
		}	
		
	return $file_content;
}

function sendEmail($eMailContent, $order_no, $status)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";
		require_once $path_include;		
		$mail = new PHPMailer;		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'cosmepar.nextmp.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'order3@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'AppealVicesCrashJoyous77';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'order3@cosmeparadise.com';
		$mail->FromName = 'SBN order Notice';
		//$mail->AddAddress('support@cosmeparadise.com', 'Berry Lai');  // Add a recipient
		//$mail->AddAddress('order2@cosmeparadise.com', 'cosme order2'); // Name is optional	
		$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters		
		$mail->IsHTML(true);                                  // Set email format to HTML		
		$mail->Subject = 'Order number:'.$order_no.' to SBN status:'.$status;
		$mail->Body    = $eMailContent;		
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo ' Message has been sent <br>';		 
	 } 
	 
?>