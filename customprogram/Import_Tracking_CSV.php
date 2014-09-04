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
/////////////// call SQL read////////////////
$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');		
/////////////// call SQL write///////////////
$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');	


$today =  date("Y/m/d");
echo $today." ".date("Y/m/d H:i:s")."<BR>";
$threedaybefore = date( "Y/m/d", strtotime ("-5 day", strtotime($today) ) ); 
//$SBN_url = "http://affiliate.strawberrynet.com/affiliate/cgi/statusResponse.aspx?siteId=cosmeparadise&dateFrom=".$threedaybefore."&dateTo=".$today."&remark=1&product=1";
$SBN_url = "http://www.cosmeparadise.com/customprogram/SBNORDERXML.php";
connect_SBN_shipment($SBN_url, $connection_read, $connection_write);

////////////////////////////function/////////////////////////////////
function connect_SBN_shipment($SBN_url, $connection_read, $connection_write) 
{ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $SBN_url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	$file_content = curl_exec($ch); 
	curl_close($ch);	
	$xml = simplexml_load_string($file_content);
	foreach($xml->children() as $Order){
		echo "<br>For order:".$Order->OrderNumber."--".$Order->ShipmentStatus."--".$Order->OrderDate."--".$Order->ShipmentRefNo1."--".$Order->affiliateRefNo."--".$Order->Courier1."<Br>";	
		
		if($Order->affiliateRefNo == "")
				continue;	//skip non-website order
		$orderNo = $Order->affiliateRefNo;

		///////////////check SBN_order_shipped_cancelled status///////////////
		$SBN_order_shipped_cancelled = "";
		$sql = "SELECT status FROM SBN_order_shipped_cancelled WHERE order_no =".$orderNo;
		$SBN_order_shipped_cancelled = $connection_read->fetchOne($sql);
		if($SBN_order_shipped_cancelled == "Dispatched" || $SBN_order_shipped_cancelled == "Cancelled due to out of stock")
			continue;
		///////////////////////////////////////////////////
			
		/////////////get shipped order ///////////
		if($Order->ShipmentStatus == "Partial Shipment" || $Order->ShipmentStatus == "Dispatched")
		{	
			$productlist = array();
			foreach($Order->children() as $product){
				$SBNID = (int)$product->ProductID;
				if($SBNID > 0)
					array_push($productlist, $SBNID);
			}			
			//print_r($productlist);
			
			$trackingNum = $Order->ShipmentRefNo1;
			$carrierTitle = "Registered Post";
			
			completeAndShip($orderNo, $trackingNum, $carrierTitle, $productlist, $connection_read, $connection_write);
		}
		else if( strpos($Order->ShipmentStatus, "Cancelled") !== false)		
		{	///////////////send email to notify Cosmeparadis///////////
			sendEmail($orderNo, $Order->ShipmentStatus, "Cancelled");			
			//////////////Insert record into database///////////////////
			$sql = "INSERT INTO SBN_order_shipped_cancelled VALUES (?, ?, ADDDATE(NOW(), INTERVAL 12 HOUR ))";
			try{
				$connection_write->query($sql, array($orderNo, $Order->ShipmentStatus));
			}catch(Exception $e)
			{	echo 'Caught exception: ', $e->getMessage(), "\n";
			}
		
		}
	}
}

  function completeAndShip($orderNo, $trackingNum, $carrierTitle, $productlist, $connection_read, $connection_write){
        $email = true;
        $allsbn = true;
        $existsbn = false;
        $includeComment = false;        
        $comment = "";
 
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderNo);
		//echo $order->getShippingDescription()."<br>";
		echo $order->getId();
        
        $convertor = Mage::getModel('sales/convert_order');
		$shipment = $convertor->toShipment($order);
		
		
        foreach ($order->getAllItems() as $orderItem) {
			$sbn_id = "";
			if (!$orderItem->getQtyToShip()) {
				continue;
			}
					
			if($orderItem->getProductType() != "virtual")
			{	
				$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
			 
				//////////////////////////get current_product_type///////////////////////////////////
				if ($product->getResource()->getAttribute('current_product_type'))
					if($current_product_type = $product->getResource()->getAttribute('current_product_type')->getFrontend()->getValue($product) != "SBN")
					{	$allsbn = false;
						continue;
					}else
						$existsbn = true;
				//////////////////////////get sbn_pid ///////////////////////////////////
				if($product->getResource()->getAttribute('sbn_pid'))
				{if($product->getResource()->getAttribute('sbn_pid')->getFrontend()->getValue($product) != null)
				  {$sbn_id=$product->getResource()->getAttribute('sbn_pid')->getFrontend()->getValue($product);
						if(in_array($sbn_id, $productlist))
						{	$existsbn = true;
							$item = $convertor->itemToShipmentItem($orderItem);
							$qty = $orderItem->getQtyToShip();
							$item->setQty($qty);
							$shipment->addItem($item);
						}
				  }	
				}				
				//echo $orderItem->getSku()."<br>"; 
			}
			else
			{	echo $orderItem->getProductId()." is free gift";
				if($allsbn == true && $existsbn == true)
				{	$item = $convertor->itemToShipmentItem($orderItem);
					$qty = $orderItem->getQtyToShip();
					$item->setQty($qty);
					$shipment->addItem($item);	
					$includeComment = true;
					$comment = "Please kindly note your order has been shipped and we will ship out the free gift to you separately by airmail.";
				}
			}
        }	
		
		if($existsbn == true)
		{
		if($carrierTitle == 'Registered Airmail')
			$carrier = 'tracker1';
		else if($carrierTitle == 'EMS')
			$carrier = 'tracker2';
		else if($carrierTitle == 'Toll')
			$carrier = 'tracker3';
		else if($carrierTitle == 'TNT')
			$carrier = 'tracker4';
		else if($carrierTitle == 'Registered Post')
			$carrier = 'tracker5';
		else
			$carrier = 'custom';
			
		/////////////////////////check if Express Shipping, then change title///////////////////
		if(strpos($order->getShippingDescription(), "Express Shipping") !== false)
					$carrierTitle = "Express Shipping";
					
        $data = array();
        $data['carrier_code'] = $carrier;
        $data['title'] = $carrierTitle;
        $data['number'] = $trackingNum;
 
        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
        $shipment->addTrack($track);
 
        $shipment->register();
        $shipment->addComment($comment, $email && $includeComment);
        $shipment->setEmailSent(true);
        $shipment->getOrder()->setIsInProcess(true);
 
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();
 
        $shipment->sendEmail($email, ($includeComment ? $comment : ''));
		////////If all SBN, that mean all products have been shipped. Then complete order/////////
		if($allsbn == true)
		{	$order->setStatus('Complete');			
			$order->addStatusToHistory($order->getStatus(), 'Order has Shipped and completed it has Shipped Automatically via SBN shipment update Program.', false);				
			//////////////Insert record into database///////////////////
			$sql = "INSERT INTO SBN_order_shipped_cancelled VALUES (?, ?, ADDDATE(NOW(), INTERVAL 12 HOUR ))";
			try{
				$connection_write->query($sql, array($orderNo, "Order Dispatched & Completed"));
			}catch(Exception $e)
			{	echo 'Caught exception: ', $e->getMessage(), "\n";
			}
        }else
			$order->addStatusToHistory($order->getStatus(), 'Some items have been shipped Automatically via SBN shipment update Program.', false);
		
        $shipment->save();
		///////////////send email to notify Cosmeparadis///////////
		sendEmail($orderNo, "Dispatched", "Dispatched");		
		echo "Shipment shipped!";
		}else
			echo "All Order items has been shipped, cannot do it again.";
}

function sendEmail($order_no, $ShipmentStatus, $subject_status)
	 {		
		//////////////////////////////////////////////////////////////////////		
		$path_include = "./phpMailer/class.phpmailer.php";
		require_once $path_include;		
		$mail = new PHPMailer;		
		$mail->IsSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'cosmepar.nextmp.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'order2@cosmeparadise.com';                            // SMTP username
		$mail->Password = 'AppealVicesCrashJoyous77';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted		 
		$mail->From = 'order2@cosmeparadise.com';
		$mail->FromName = 'SBN order '.$subject_status;
		//$mail->AddAddress('support@cosmeparadise.com', 'Berry Lai');  // Add a recipient
		//$mail->AddAddress('order2@cosmeparadise.com', 'cosme order2'); // Name is optional	
		$mail->AddBCC('support@cosmeparadise.com', 'Berry Lai');		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters		
		$mail->IsHTML(true);                                  // Set email format to HTML		
		$mail->Subject = 'SBN items in Order number:'.$order_no.' status:'.$subject_status;
		$mail->Body    = 'SBN items in Order number:'.$order_no.' has been '.$ShipmentStatus;		
		
		if(!$mail->Send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;		
		}
		else
			echo 'Message has been sent <br>';		 
	 } 

?>