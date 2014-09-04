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
##########################################################################################
# Here is abandon cart programme. 
# It will load Magento core file, and read directly from a Magento database table
# Berry - Edition
# 
##########################################################################################

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

// Add VAT to prices
if (isset($_GET['add_vat'])){
	$add_vat = ($_GET['add_vat'] == "on") ? "on" : "off";
}
else {
	$add_vat = "off";
}

// Get current date
$datetime = date("Y-m-d G:i:s");
//////////pre set data to get ready//////
$emailfile = file_get_contents('abandon.html');
try{

	// display all abandon cart with items
	$sql = "SELECT Abandon_cart.entity_id AS cart_id, Abandon_cart.created_at, Abandon_cart.created_at, TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , Abandon_cart.created_at ) AS TD, Abandon_cart.items_count, Abandon_cart.customer_email, Abandon_cart.customer_firstname, Abandon_cart.customer_lastname, Abandon_cart.quote_currency_code
FROM sales_flat_quote Abandon_cart
WHERE Abandon_cart.is_active =1 AND customer_id IS NOT NULL
AND Abandon_cart.items_count >0
AND TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , Abandon_cart.created_at ) >=  TIME('23:00:00')
AND TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , Abandon_cart.created_at ) <  TIME('23:59:59')
ORDER BY Abandon_cart.entity_id ASC";
//union.programmer@gmail.com   info@cosmeparadise.com kittycheungkm@gmail.com		AND Abandon_cart.customer_email =  'kittycheungkm@gmail.com'
	$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//////////// Make connection to call SQL read
	$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//////////// Make connection to call SQL write
	///////////////////get abandon cart detail////////////////////
	foreach ($connection_read->fetchAll($sql) as $Abandon_cart) {
		$tempemailfile = $emailfile;
		$ItemData = "<tr><td width='350px' align='center'>Name</td><td align='center' width='100px'>Image</td><td align='left' width='20px'>Qty</td><td align='center' width='80px'>Price(".$Abandon_cart['quote_currency_code'].")</td><td align='right' width='80px'>Subtotal(".$Abandon_cart['quote_currency_code'].")</td></tr>";
		$ItemData .= "<tr><td colspan='5'><hr></td></tr>";
		/////////////////gen abandon items details///////////////////////
		$GetItemSQL = "SELECT product_id, qty, IFNULL(prIce_incl_tax, 0.00) AS currency_price, row_total FROM sales_flat_quote_item WHERE quote_id =".$Abandon_cart['cart_id'];
			foreach ($connection_read->fetchAll($GetItemSQL) as $Abandon_cart_item_id) {	
				$PRODUCT = array();
		
				$PRODUCT = function_to_get_product_details($Abandon_cart_item_id['product_id']);
				
				if (isset($GROUPED[$productId])) {
					$GROUPED_PRODUCT = function_to_get_product_details($GROUPED[$productId]);					
					if ($GROUPED_PRODUCT['prod_url'] != "") {
						$PRODUCT['prod_url'] = $GROUPED_PRODUCT['prod_url'];
					}
					if ($GROUPED_PRODUCT['prod_image'] != "") {
						$PRODUCT['prod_image'] = $GROUPED_PRODUCT['prod_image'];
					}
					unset($GROUPED_PRODUCT);
				}
				
				//$ItemData .= $Abandon_cart['cart_id']."--".$Abandon_cart['TD']."--".$Abandon_cart['items_count']."--".$Abandon_cart['customer_email']."--".$Abandon_cart['customer_firstname']."--".$Abandon_cart['customer_lastname']."--".$PRODUCT['prod_id']."--".$PRODUCT['prod_name']."--".$PRODUCT['prod_url']."--".$PRODUCT['prod_image']."<br>";
				$ItemData .= "<tr>";
				$ItemData .= "<td width='350px'><a href='".$PRODUCT['prod_url']."'>".$PRODUCT['prod_name']."</a></td>";
				$ItemData .= "<td><img alt='".$PRODUCT['prod_name']."' width='100' src='".$PRODUCT['prod_image']."' name='".$PRODUCT['prod_name']."' /></td>";
				$ItemData .= "<td align='center'> ".number_format($Abandon_cart_item_id['qty'],0)." </td>";
				$ItemData .= "<td align='center'> ".number_format($Abandon_cart_item_id['currency_price'], 2, ".", ",")." </td>";
				$ItemData .= "<td align='right'> ".number_format($Abandon_cart_item_id['row_total'], 2, ".", ",")." </td>";
				$ItemData .= "</tr>";
				$ItemData .= "<tr><td colspan='5'><hr></td></tr>";
			}
		$tempemailfile = str_replace("{{var customer_name}}",$Abandon_cart['customer_firstname']." ".$Abandon_cart['customer_lastname'],$tempemailfile);
		$tempemailfile = str_replace("{{var products}}",$ItemData,$tempemailfile);
		$tempemailfile = str_replace("{{var store_url}}","http://www.cosmeparadise.com/?utm_source=Abandon_cart&utm_medium=email&utm_content=Abandon_Cart_Email&utm_campaign=".$Abandon_cart['customer_email']."_30M",$tempemailfile);
		$tempemailfile = str_replace("{{var login_url}}","https://www.cosmeparadise.com/customer/account/login/?utm_source=Abandon_cart&utm_medium=email&utm_content=Abandon_Cart_Email&utm_campaign=".$Abandon_cart['customer_email']."_30M",$tempemailfile);
		$tempemailfile = str_replace("{{var open_image_url}}","http://www.cosmeparadise.com/customprogram/abandon_record.php?user_email=".$Abandon_cart['customer_email']."&cart_id=".$Abandon_cart['cart_id']."&type=30M",$tempemailfile);
		//echo "Start<hr>".$tempemailfile;
		sendEmail($tempemailfile,$Abandon_cart['customer_firstname']." ".$Abandon_cart['customer_lastname'],$Abandon_cart['cart_id'],$Abandon_cart['customer_email'],$connection_write);
	}
	echo "finished";
	/*
	
	/////////////open file and write into it///////////////
	$filename = "../datafeed/cosmedropship.txt";
	$ourFileHandle = fopen($filename, 'w') or die("can't open file");
	fputs($ourFileHandle, $line);
	//close file after write the content into it
	fclose($ourFileHandle);
	
	echo "success. check file link <a href=\"./cosmedropship.txt\">Here</a>";*/
}
catch(Exception $e){
	die($e->getMessage());
}

 function sendEmail($eMailContent,$Customer_Name,$cart_id,$email,$connection_write)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";

		// Include configuration file
		if(!file_exists($path_include)) {
			exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
		}
		else {
			echo "get require!<br>";
			require_once $path_include;
		}

		$mail = new PHPMailer;
		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'cosmepar.nextmp.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'marketing@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'CrustySunCotesTenses17';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = 'marketing@cosmeparadise.com';
		$mail->FromName = 'Cosme Paradise';
		$mail->AddAddress($email, $Customer_Name);  // Add a recipient
		//$mail->AddAddress('union.programmer@gmail.com');               // Name is optional
		//$mail->AddReplyTo('support@cosmeparadise.com', 'Information');
		//$mail->AddCC('cc@example.com');
		//$mail->AddBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->IsHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'Pending Purchase in your shopping cart from CosmeParadise.com';
		$mail->Body    = $eMailContent;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		exit;
		}
		
		echo 'Message has been sent';
	 /*$sCharset = 'utf-8';
	 
	 // To
	 //$sMailTo = $email;	 
	 $sMailTo = 'support@cosmeparadise.com,union.programmer@gmail.com';
	 ///$sMailTo = 'Berry.lai@dropshipgs.com';//, Joe Chan <joe.chan@dropshipgs.com>';
	
	 //$sMailFrom = 'Cosme Paradise - Marketing <marketing@cosmeparadise.com>';
	 $sMailFrom = 'Cosme Paradise <support@cosmeparadise.com>';
	 // subject
	 $sSubject = "Pending Purchase in your shopping cart from CosmeParadise.com";
	 // content
	 $sMessage = $eMailContent;
	 
	 /////////////////////SEND EMAIL///////////////////////
		 $sHeaders = "MIME-Version: 1.0\r\n" .
	 			"Content-type: text/html; charset=$sCharset\r\n" .
	 			"From: $sMailFrom\r\n";
	 
	 //send email
	 $mail_sent = @mail($sMailTo, $sSubject, $sMessage, $sHeaders);
	 */
	 // insert record
		$sql = "INSERT INTO Abandon_Email_Record (user_email, Type, cart_id, Send_Date) VALUES ('".$email."', '30M', ".$cart_id.", ADDDATE( NOW( ) , INTERVAL 12 HOUR ))";		
		$connection_write->query($sql);
	
	 //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
	// echo $mail_sent ? "Mail sent!" : "Mail failed";
	 
	 }
	 
function function_to_get_product_details($product_id) {

	global $Mage, $add_vat;
	$product = Mage::getModel('catalog/product');
	$product->load($product_id);
	$prod_sku = $product->getSku();	
	$prod_id = $product->getId();
	$prod_name = $product->getName();
	//$prod_url = function_to_get_product_url($product->getProductUrl());
	$prod_url = $product->getProductUrl();
	$prod_image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();	
	$prod_price = function_to_get_product_price($product);

	// Add VAT to prices
	if ($add_vat == "on") {
		$prod_price = $prod_price * $vat_value;
	}

	// Clean product name (new lines)
	$prod_name = str_replace("\n", "", strip_tags($prod_name));
	$prod_name = str_replace("\r", "", strip_tags($prod_name));
	$prod_name = str_replace("\t", " ", strip_tags($prod_name));
	
	if (strpos($prod_image, "no_selection")) {
		$prod_image = "";
	}

	$RESULT = array();
	
	$RESULT['prod_sku'] = $prod_sku;
	$RESULT['prod_id'] = $prod_id;
	$RESULT['prod_name'] = $prod_name;
	$RESULT['prod_url'] = $prod_url;
	$RESULT['prod_image'] = $prod_image;
	$RESULT['prod_price'] = $prod_price;

	unset($product);
	
	return $RESULT;
}

// Function to return the Product URL based on your product ID
function function_to_get_product_url($product_url){
	$current_file_name = basename($_SERVER['REQUEST_URI']);
	$product_url = str_replace($current_file_name, "index.php", $product_url);
	$product_url = str_replace("myshopping_magento", "index", $product_url);
	
	// Eliminate id session 
	$pos_SID = strpos( $product_url, "?SID");
	if ($pos_SID) {
		$product_url = substr($product_url, 0, $pos_SID);
	}
	return $product_url;
	
}

function function_to_get_product_price($product) {

	$_taxHelper  = Mage::helper('tax');

	if ( $product->getSpecialPrice() && (date("Y-m-d G:i:s") > $product->getSpecialFromDate() || !$product->getSpecialFromDate()) &&  (date("Y-m-d G:i:s") < $product->getSpecialToDate() || !$product->getSpecialToDate())){
		$price = $product->getSpecialPrice();
	} else {
		$price = $product->getPrice();
	}

	$price = $_taxHelper->getPrice($product, $price, true);

	return $price;
}


?>