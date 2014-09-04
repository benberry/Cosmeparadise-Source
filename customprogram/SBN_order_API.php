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

$process = $_GET['process'];

$price_updated = "";
$emailcontent = "";
Mage::app($store);

///////////////////////////////////////////////////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//// call SQL read
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//// call SQL write

///////////////get sbn_pid attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'sbn_pid' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$sbn_pid_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get union_sku attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'union_sku' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$union_sku_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

///////////////get current_product_type attribute ID///////////////
$sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'current_product_type' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product')";
$current_product_type_attribute = $connection_read->fetchOne($sql);
///////////////////////////////////////////////////

if($process == "GOAPI")
{	echo "Here will run the order API programme.<br>";
	$showprocesscondition = "";
}
else if($process == "check")
{	echo "Here will simulate order API programme.<br>";
	$showprocesscondition = "";
}
else
{	echo "Here will show SBN order which status are still 'checking', and need to do security check.<br>";
	$showprocesscondition = "AND order_to_sbn.securitycheck='Checking'";
}
$sql = "SELECT order_to_sbn.entity_id AS order_id, order_to_sbn.increment_id AS order_no, order_to_sbn.created_at,ADDDATE(NOW(), INTERVAL 4 HOUR),TIMEDIFF(ADDDATE(NOW() , INTERVAL 4 HOUR), order_to_sbn.created_at) AS update_diff, order_to_sbn.state, order_to_sbn.shipping_description, order_to_sbn.total_paid, order_to_sbn.securitycheck, payment.method, payment.additional_information, billing_address.street AS bill_address,
shipping_address.firstname, shipping_address.lastname, shipping_address.company,
shipping_address.street AS address, shipping_address.region AS state, shipping_address.city,
shipping_address.postcode,country.country_name,shipping_address.telephone, shipping_address.fax, shipping_address.email
FROM sales_flat_order order_to_sbn
INNER JOIN sales_flat_order_address shipping_address ON shipping_address.parent_id = order_to_sbn.entity_id AND shipping_address.address_type = 'shipping'
INNER JOIN sales_flat_order_address billing_address ON billing_address.parent_id = order_to_sbn.entity_id AND billing_address.address_type = 'billing'
INNER JOIN geoip_countrylist country ON country.country_code = shipping_address.country_id
LEFT JOIN sales_flat_order_payment payment ON payment.parent_id = order_to_sbn.entity_id  
WHERE order_to_sbn.state='processing' AND order_to_sbn.total_paid IS NOT NULL
".$showprocesscondition." AND order_to_sbn.entity_id >= 6007	
AND order_to_sbn.increment_id NOT IN (SELECT order_no FROM SBN_order)
ORDER BY order_id ASC";
//FR IT	DE	MC
$filter_country = array('France', 'Italy', 'Monaco', 'Germany');
		
//AND TIMEDIFF(ADDDATE(NOW(), INTERVAL 4 HOUR), order_to_sbn.created_at) <  TIME('94:59:59')
foreach ($connection_read->fetchAll($sql) as $SBN_order) {	///get all paid processing orders within 24 hours
	/*$check_gift_SQL = "SELECT COUNT(item_id) AS gift_qty FROM sales_flat_order_item items WHERE items.order_id =".$SBN_order['order_id']." AND is_virtual IS NULL AND name LIKE '%FREE GIFT%'";
	$gift_qty = $connection_read->fetchOne($check_gift_SQL);
	if( $gift_qty == 0)	///////////check if have free gift, if yes then continue/////////
	*/
	///////////////Check if a black list email///////////////
	$email_count = 0;
	$sql = "SELECT COUNT(email) FROM SBN_filter_email WHERE email = '".$SBN_order['email']."'";
	$email_count = $connection_read->fetchOne($sql);
	if($email_count > 0)
	{	
		if($process == "GOAPI")
		{	if($SBN_order['securitycheck'] == "Fraud")
			{	echo "Order:".$SBN_order['order_no']." use a email:".$SBN_order['email']." in black list! <br>";
				sendEmail("Order:".$SBN_order['order_no']." use a email:".$SBN_order['email']." in black list!", $SBN_order['order_no'], "Email in Black list");
				$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
				$connection_write->query($Insert_SBN_order, array($SBN_order['order_no'], "no product be done", "Email in Black list"));	
			}
		}
		else 
		{	echo "Order:".$SBN_order['order_no']." use a email:".$SBN_order['email']." in black list! Please set security check status to 'Fraud'<br>";
		}
		continue;
	}
	///////////////////////////////////////////////////
	
		$SBN_product_array = array();
		$SKU_SBN_product_array = array();
		$Union_Sku_array = array();
		$Exist_Union_Sku = false;
		$Partial_Exist_Union_Sku = false;
		$ALLSBN = true;
		$IsSBN = false;
		$SBN_shipping = 0;
		$Ship_From_SBN = false;
		$API_reponse = "";
		$getItemSQL = "SELECT items.order_id, items.product_id, items.is_virtual, items.name, items.qty_ordered, cpev.value AS SBNID, cpei.value, SBN_products.SBN_Category, union_sku.value AS u_sku, items.sku 
		FROM sales_flat_order_item items 
		LEFT JOIN catalog_product_entity_varchar cpev ON cpev.entity_id = items.product_id AND cpev.attribute_id = ".$sbn_pid_attribute."
		LEFT JOIN catalog_product_entity_int cpei ON cpei.entity_id = cpev.entity_id AND cpei.attribute_id = ".$current_product_type_attribute." AND cpei.value = (SELECT option_id FROM eav_attribute_option_value WHERE value = 'SBN' AND store_id = 0)
		LEFT JOIN catalog_product_entity_varchar union_sku ON union_sku.entity_id = items.product_id AND union_sku.attribute_id = ".$union_sku_attribute." 
		LEFT JOIN SBN_products ON SBN_products.ProdId = cpev.value
		WHERE items.order_id = ".$SBN_order['order_id']." AND cpei.value IS NOT NULL
		AND name NOT LIKE '%FREE GIFT%'";		
		foreach ($connection_read->fetchAll($getItemSQL) as $Order_items) {	//////get items from order/////
			if($Order_items['SBNID'] != NULL)
			{	
				/*if($Order_items['u_sku'] != NULL)
				{	array_push($Union_Sku_array, $Order_items['sku']);	
					$Exist_Union_Sku = true;
					$ALLSBN = false;					
				}
				else
				{*/
					for($qtycount = 1; $qtycount <= $Order_items['qty_ordered']; $qtycount++)
					{	array_push($SBN_product_array, $Order_items['SBNID']);	
						array_push($SKU_SBN_product_array, $Order_items['sku']);	
					}
					$IsSBN = true;
					
					////////////check if there any non Fragrance product///////////
				if( strpos($Order_items['SBN_Category'], "Fragrance") === false && $Order_items['SBN_Category'] != null)
					$Ship_From_SBN = true;
				//}				
			}
			else
				$ALLSBN = false;
		}	
		
		//$Union_Sku_s = implode(",",$Union_Sku_array);		
		$SKU_SBN_product = implode(",",$SKU_SBN_product_array);		
		
		if($IsSBN == true )//&& $Ship_From_SBN == true)	///////////if include SBN product/////////////
		{	$SBN_product_IDs = implode(",",$SBN_product_array);
			/////////////////filter out banned country//////////////////
			if(in_array($SBN_order['country_name'], $filter_country))
			{	if($process == "GOAPI")
				{	
					try{
					sendEmail("Order ship to ".$SBN_order['country_name']." has banned from SBN", $SBN_order['order_no'], "Banned Country in SBN");
					$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
					$connection_write->query($Insert_SBN_order, array($SBN_order['order_no'], $SBN_product_IDs, "Banned Country in SBN"));		
					}catch(Exception $e)
					{	echo 'Caught exception: ', $e->getMessage(), "\n";
					}
				}else if($process == "check")
					echo "Order:".$SBN_order['order_no']." ship to ".$SBN_order['country_name']." has banned from SBN!<br>";
				continue;
			}
			////////////////check if express shipping///////////
			//echo $SBN_order['shipping_description']." -- ".strpos($SBN_order['shipping_description'],"Express Shipping")."<BR>";
			if( strpos($SBN_order['shipping_description'], "Express Shipping") !== false)
				$SBN_shipping = 1;
			
			//if($ALLSBN == true)	/////////if all productd from SBN//////
			//{	
			//$SBN_product_IDs = implode(",",$SBN_product_array);
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
						$shiptoaddress3 = substr($temp_shiptoaddress2, $pos2, strlen($temp_shiptoaddress2));
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
				$SBN_url = "http://affiliate.strawberrynet.com/affiliate/CGI/receiveResponse.aspx?siteID=cosmeparadise&firstname=".rawurlencode(trim($SBN_order['firstname']))."&lastname=".rawurlencode(trim($SBN_order['lastname']))."&compname=".rawurlencode(trim($SBN_order['company']))."&addr1=".rawurlencode($shiptoaddress1)."&addr2=".rawurlencode($shiptoaddress2)."&addr3=".rawurlencode($shiptoaddress3)."&state=".rawurlencode($SBN_order['state'])."&city=".rawurlencode(trim($SBN_order['city']))."&postalcode=".rawurlencode(trim($SBN_order['postcode']))."&country=".rawurlencode($SBN_order['country_name'])."&tel=".rawurlencode($telephone)."&fax=&affiliateref=".rawurlencode($SBN_order['order_no'])."&products=".rawurlencode($SBN_product_IDs)."&shipping=".$SBN_shipping;
				if($process == "GOAPI")
				{	
					if($SBN_order['securitycheck'] == "Authorize")					
					{$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_product_IDs,$connection_write);
					 $Partial_Exist_Union_Sku = true;
					 echo $API_reponse."<br>".$SBN_url;	//urlencode($SBN_url)
					}/*else if($SBN_order['method'] == "paypal_express" && strpos($SBN_order['additional_information'], "verified") !== false && $SBN_order['total_paid'] < 300 )
					 {$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_product_IDs,$connection_write);
					 $Partial_Exist_Union_Sku = true;
					 $Update_securitycheck = "UPDATE sales_flat_order SET securitycheck='Authorize' WHERE entity_id = ".$SBN_order['order_id'];
					 $connection_write->query($Update_securitycheck);
					 echo $API_reponse." - auto Approved<br>".$SBN_url;	//urlencode($SBN_url)
					}else if($SBN_order['method'] == "paypal_express" && $SBN_order['total_paid'] < 150 )
					 {$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_product_IDs,$connection_write);
					 $Partial_Exist_Union_Sku = true;
					 $Update_securitycheck = "UPDATE sales_flat_order SET securitycheck='Authorize' WHERE entity_id = ".$SBN_order['order_id'];
					 $connection_write->query($Update_securitycheck);
					 echo $API_reponse." - auto Approved<br>".$SBN_url;	//urlencode($SBN_url)
					}else if($SBN_order['method'] == "hosted_pro" && $SBN_order['total_paid'] < 100 )
					 {	if($SBN_order['address'] == $SBN_order['bill_address'])
						{$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_product_IDs,$connection_write);
						$Partial_Exist_Union_Sku = true;
						$Update_securitycheck = "UPDATE sales_flat_order SET securitycheck='Authorize' WHERE entity_id = ".$SBN_order['order_id'];
						$connection_write->query($Update_securitycheck);
						echo $API_reponse." - auto Approved<br>".$SBN_url;	//urlencode($SBN_url)
						}	
					}*/
				}
				else if($process == "check")
				{
					/*if($Exist_Union_Sku == true)
					{
						echo "Order Number:".$SBN_order['order_no']." sku:".$Union_Sku_s." is(are) union products, only (".$SKU_SBN_product.") will pass to API, please manually create order in SBN for that(those) product(s). -- ".$SBN_url."<br>";						
					}					
					else*/
					 echo $SBN_order['order_no']." -- ".$SBN_url."<br>";	//urlencode($SBN_url)
				}
				else
				{	if($SBN_order['method'] == "paypal_express" && strpos($SBN_order['additional_information'], "verified") !== false && $SBN_order['total_paid'] > 300 )
					 {echo "Order Number:".$SBN_order['order_no']." is paypal order, payer status verified but amount >300, need to do security check.<br>";
					}else if($SBN_order['method'] == "paypal_express" && $SBN_order['total_paid'] > 150 )
					 {echo "Order Number:".$SBN_order['order_no']." is paypal order, but amount >150, need to do security check.<br>";
					}else if($SBN_order['method'] == "hosted_pro" && $SBN_order['total_paid'] < 100 )
					 {	if($SBN_order['address'] != $SBN_order['bill_address'])
						{echo "Order Number:".$SBN_order['order_no']." is hosted credit order, amount < 100, but billing and shipping address not match, need to do security check.<br>";
						}	
					}else if($SBN_order['method'] == "hosted_pro" && $SBN_order['total_paid'] > 100 )
					 {	echo "Order Number:".$SBN_order['order_no']." is hosted credit order, but amount > 100, need to do security check.<br>";
					}
					/*else if($Exist_Union_Sku == true)
					{
						echo "Order Number:".$SBN_order['order_no']." sku:".$Union_Sku_s." is(are) union products, it won't send to API, please manually create order in SBN for that(those) product(s). <br>";
					
					}	*/					
						
				}
				
				////////////Send email for partial exist union sku SBN order////////////
				/*if($Partial_Exist_Union_Sku == true && $Exist_Union_Sku == true)				
				{	echo " --- sku:".$Union_Sku_s." has union sku, only ".$SKU_SBN_product." will pass to API<br>";
					sendEmail("sku:".$Union_Sku_s." has union sku, only ".$SKU_SBN_product." will pass to API", $SBN_order['order_no'], "Partial union SKU");
					$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
					$connection_write->query($Insert_SBN_order, array($SBN_order['order_no'], "sku skipped:".$Union_Sku_s, "Banned union SKU"));	
				}*/
					
			/*}else{
				$SBN_product_IDs = implode(",",$SBN_product_array);
				$SBN_url = "http://affiliate.strawberrynet.com/affiliate/CGI/receiveResponse.aspx?siteID=cosmeparadise&firstname=Miuna&lastname=Yu&compname=".rawurlencode('Cosme Paradise')."&addr1=".rawurlencode('502 Yee Kuk Industrial Centre')."&addr2=".rawurlencode('555 Yee Kuk Street')."&addr3=".rawurlencode('Kowloon')."&city=".rawurlencode('Hong Kong')."&country=China&tel=".rawurlencode('27208668-194')."&affiliateref=".rawurlencode($SBN_order['order_no'])."&products=".rawurlencode($SBN_product_IDs)."&shipping=".$SBN_shipping;
				$API_reponse = connect_SBN_API($SBN_url,$SBN_order['order_no'],$SBN_product_IDs,$connection_write);
				echo "<br>".$API_reponse."<br>".$SBN_url;
			}			*/
		}
		else if($IsSBN == true && $Ship_From_SBN == false)
		{	if($process == "GOAPI")
			{	try{
				$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
				$connection_write->query($Insert_SBN_order, array($SBN_order['order_no'], "", "Fragrance Order, do outside SBN"));
				}catch(Exception $e)
				{	echo 'Caught exception: ', $e->getMessage(), "\n";
				}
				sendEmail("It's just fragrance order, purchase from local store!", $SBN_order['order_no'], "Transfer to Local");				
			}
			else if($process == "check")
				echo $SBN_order['order_no']." -- will do local purchase!<br>";
		}
		/*else if($Exist_Union_Sku == true)
		{
			if($process == "GOAPI")
			{	
				try{
				echo "sku:".$Union_Sku_s." has union sku, this order:".$SBN_order['order_no']." won't pass to API<br>";
				sendEmail("sku:".$Union_Sku_s." has union sku, this order won't pass to API", $SBN_order['order_no'], "Banned as all union SKU");
				$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
				$connection_write->query($Insert_SBN_order, array($SBN_order['order_no'], "sku skipped:".$Union_Sku_s, "Banned as all union SKU"));		
				}catch(Exception $e)
				{	echo 'Caught exception: ', $e->getMessage(), "\n";
				}
			}else if($process == "check")
				echo "sku:".$Union_Sku_s." has union sku, this order:".$SBN_order['order_no']." won't pass to API<br>";
			continue;		
		}*/
		///////////end if include SBN product/////////////	
}

///////////////////////////function///////////////////////////
function connect_SBN_API($SBN_url,$order_no,$SBN_product_IDs,$connection_write) 
{ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $SBN_url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	$file_content = curl_exec($ch); 
	curl_close($ch);	
	$xml = simplexml_load_string($file_content);
	//print_r($xml);
	//echo "<br><br>".$xml->status."<br>";	
	try{
		$Insert_SBN_order = "INSERT INTO SBN_order VALUES (?, ?, ?, ADDDATE(NOW(), INTERVAL 13 HOUR ))";
		$connection_write->query($Insert_SBN_order, array($order_no, $SBN_product_IDs, $xml->status)); 	
		sendEmail($file_content, $order_no, $xml->status);
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
		$mail->Host = 'sipau2-01.nexcess.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'order3@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'AppealVicesCrashJoyous77';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'order3@cosmeparadise.com';
		$mail->FromName = 'SBN order Notice';
		$mail->AddAddress('sbnorder@cosmeparadise.com', 'SBN order');  // Add a recipient
		$mail->AddAddress('order2@cosmeparadise.com', 'cosme order2'); // Name is optional	
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