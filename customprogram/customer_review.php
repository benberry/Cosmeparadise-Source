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
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
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
$email_count=0;
//////////pre set data to get ready//////
$emailfile = file_get_contents('customer_review.html');
try{
	
	// display all abandon cart with items
	$sql = "SELECT order_complete.entity_id AS cart_id, order_complete.increment_id, order_complete.customer_id,order_complete.created_at, order_complete.updated_at, NOW(), DATEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , order_complete.updated_at ) AS update_diff, order_complete.state, shipping_address.firstname, shipping_address.lastname, shipping_address.email
FROM sales_flat_order order_complete
INNER JOIN sales_flat_order_address shipping_address ON shipping_address.parent_id = order_complete.entity_id AND shipping_address.address_type = 'shipping' AND shipping_address.email IS NOT NULL
WHERE order_complete.customer_id IS NOT NULL AND order_complete.state='complete'
AND DATEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , order_complete.updated_at ) =  10
ORDER BY cart_id ASC";
/*
SELECT order_complete.entity_id AS cart_id, order_complete.customer_id,order_complete.created_at, order_complete.updated_at, NOW(), TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , order_complete.updated_at ) AS update_diff, order_complete.state
FROM sales_flat_order order_complete
WHERE order_complete.customer_id IS NOT NULL AND order_complete.state='complete'
AND TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , order_complete.updated_at ) >  '00:01:00'
AND TIMEDIFF( ADDDATE( NOW( ) , INTERVAL 4 HOUR ) , order_complete.updated_at ) <  '88:00:00'
ORDER BY order_complete.entity_id DESC
*/
//union.programmer@gmail.com   info@cosmeparadise.com kittycheungkm@gmail.com		AND Abandon_cart.customer_email =  'kittycheungkm@gmail.com'
	$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		//////////// Make connection to call SQL read
	//$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	//////////// Make connection to call SQL write
	///////////////////get abandon cart detail////////////////////
	foreach ($connection_read->fetchAll($sql) as $customer_review) {
		sendEmail($emailfile,$customer_review['firstname']." ".$customer_review['lastname'],$customer_review['increment_id'],$customer_review['email']);
		$email_count++;
		//echo $emailfile;
		//echo "<br>".$customer_review['firstname']." ".$customer_review['lastname']." ".$customer_review['cart_id']." ".$customer_review['email'];
	}
	
}
catch(Exception $e){
	die($e->getMessage());
}
echo "Total ".$email_count." emails have sent!";

 function sendEmail($eMailContent,$Customer_Name,$increment_id,$email)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";

		// Include configuration file
		if(!file_exists($path_include)) {
			exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that this file is in the root directory, or make sure the path to the directory where the configure.php file is located is defined corectly above in $path_include variable</BODY></HTML>');
		}
		else {
			//echo "get require!<br>";
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
		//$mail->AddAddress('union.programmer@gmail.com', 'Berry Lai');               // Name is optional
		//$mail->AddReplyTo('support@cosmeparadise.com', 'Information');
		//$mail->AddCC('kittycheungkm@gmail.com');
		//$mail->AddBCC('info@cosmeparadise.com');
		$mail->AddBCC('support@cosmeparadise.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->IsHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'Write us a review! We care & your feedback matters to us. (Order-ID:'.$increment_id.')';
		$mail->Body    = $eMailContent;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo 'Cart:'.$increment_id.' Message has been sent <br>';
	
	 }

?>