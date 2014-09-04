<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
# Path to your Magento installation
define('MAGENTO', realpath('/home/cosmepar/cosmeparadise.com/html/'));
require_once(MAGENTO . '/app/Mage.php');
$app = Mage::app();
// Get default store code
$default_store = Mage::app()->getStore();
$default_store_code = $default_store->getCode();
$store = $default_store_code;

Mage::app($store);
//////////// Make connection to call SQL read //////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');	
//////////// Make connection to call SQL write //////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	

$orderid = $_GET['orderid'];
$Security_Check = $_GET['Security_Check'];
$record = false;
//Mage::log("orderid:".$orderid, null, "berry.log",true);
//Mage::log("Security_Check:".$Security_Check, null, "berry.log",true);
$line = "";
$sql = "SELECT product_update_log.* , catalog_product_entity_varchar.entity_id, catalog_product_entity_varchar.value AS SBN_id, product.sku
FROM product_update_log
LEFT JOIN catalog_product_entity_varchar ON catalog_product_entity_varchar.entity_id = product_update_log.Product_id AND catalog_product_entity_varchar.attribute_id = 148
LEFT JOIN catalog_product_entity product ON product.entity_id = product_update_log.Product_id
WHERE catalog_product_entity_varchar.value IS NULL AND origin_status=1 AND updated_status=2 AND product_update_log.attribute_id = 96 ";
	foreach ($connection_read->fetchAll($sql) as $disable_product) {		
	$line .= "Product sku:".$disable_product['sku']." has been disabled at".$disable_product['make_time_hk']."<br>";
	$record = true;
	}
	echo $line;
$sql = "TRUNCATE TABLE product_update_log";
	try{
		$connection_write->query($sql, array($Security_Check, $orderid));
	}catch(Exception $e)
	{	Mage::log("product_update_log error:".$e->getMessage(), null, "berry.log",true);
	}
	
	if($record == true)
		sendEmail($line);
	
 function sendEmail($eMailContent)
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
		$mail->FromName = 'Product update notify';
		$mail->AddAddress('report@cosmeparadise.com', 'Clara');  // Add a recipient
		//$mail->AddAddress('union.programmer@gmail.com', 'Berry Lai');               // Name is optional
		//$mail->AddReplyTo('support@cosmeparadise.com', 'Information');
		//$mail->AddCC('kittycheungkm@gmail.com');
		//$mail->AddBCC('info@cosmeparadise.com');
		$mail->AddBCC('support@cosmeparadise.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->AddAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->AddAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->IsHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'product disable log';
		$mail->Body    = $eMailContent;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo 'Cart:'.$cart_id.' Message has been sent <br>';
	
	 }
?>