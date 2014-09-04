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
$directinput = false;
if(($_POST["order_no"]) != "" || ($_POST["track_no"]) != "" ){
		if(($_POST["order_no"]) == "")
		{	echo "missing order number.";
			exit;		
		}
		if(($_POST["track_no"]) == "")
		{	echo "missing tracking number.";
			exit;		
		}
		$tempOrderno = trim($_POST["order_no"]);
		$tempTrackno = trim($_POST["track_no"]);
		//print_r($tempskulist);
		$directinput = true;
}

if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
	|| ($_FILES["file"]["type"] == "text/comma-separated-values"))
	{
	if ($_FILES["file"]["error"] > 0)
		{   echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; exit;    }
	}
  } 
else
  {
?>

<html>
<body>
<h2>Order Tracking Number Direct Import</h2>
<b>For CSV,Please keep the format: Order Number, Tracking Number, Courier(keep use "Registered Airmail", "EMS", "Toll", "TNT" or "Registered Post")<br> (The first row will be skip)</b>
<form id="usrform" action="Import_Tracking_Direct.php" method="post" enctype="multipart/form-data">
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> <br/><br />
(Below is for single input. If there is any text input, it will skip the uploaded file!)
<br />
Order Number:<input type="text" name="order_no" id="order_no" size="20"/>  
Tracking Number:<input type="text" name="track_no" id="track_no" size="20"/>
<br>
Courier:<select name="courier">
  <option value="Registered Airmail" SELECTED >Registered Airmail</option>
  <option value="EMS">EMS</option>
  <option value="Toll">Toll</option>
  <option value="TNT">TNT</option>
  <option value="Registered Post">Registered Post</option>
</select>
<br /><br />
<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>

<?php	
  exit;
  }
if($directinput == false)
{	$csvfile = $_FILES["file"]["tmp_name"];
	
	if(!file_exists($csvfile)) {
		echo "File not found.";
		exit;
	}
	
	$size = filesize($csvfile);
	if(!$size) {
		echo "File is empty.\n";
		exit;
	}
}
?>

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

 if (($handle = fopen($csvfile, "r")) !== FALSE || $directinput == true) {	//OPEN CSV
	if($directinput == true)
	{	$orderNo = $tempOrderno;
		$trackingNum = $tempTrackno;
		$carrierTitle = $_POST['courier'];
		echo $carrierTitle."<br>TEXT<br>";
		if(completeAndShip($orderNo, $trackingNum, $carrierTitle))
			echo "Finished Import tracking number for ".$orderNo;
		else
			echo "There is not item to ship for ".$orderNo;
		echo "<br> <a href='./Import_Tracking_Direct.php'>back</a>";
	}
	else
	{	//echo "CSV";
		$row=1;
		while (($data = fgetcsv($handle)) !== FALSE) {	//go through data	
			if($row > 1)
			{$orderNo = trim($data[0]);
			$trackingNum = trim($data[1]);
			$carrierTitle = trim($data[2]);
			if($orderNo != "" && $trackingNum != "" && $carrierTitle != "") 
			{	if(completeAndShip($orderNo, $trackingNum, $carrierTitle))
					echo "Finished Import tracking number for".$orderNo."<br>";
				else
					echo "There is not item to ship for ".$orderNo."<br>";
			}else
				echo $orderNo." had some blank field.";
			}
			$row++;
		}		
	}	
 }
 
 
  function completeAndShip($orderNo, $trackingNum, $carrierTitle){
        $email = true;
        //$carrier = NULL;		 
		$HasItemToShip = false;
			
        $includeComment = false;
        $comment = "Order Completed And Shipped Automatically via Automation Routines";
 
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderNo);
 
        
        $convertor = Mage::getModel('sales/convert_order');
        $shipment = $convertor->toShipment($order);
 
        foreach ($order->getAllItems() as $orderItem) {
            
            if (!$orderItem->getQtyToShip()) {
                continue;
            }
            if ($orderItem->getIsVirtual()) {
                continue;
            }
            $item = $convertor->itemToShipmentItem($orderItem);
            $qty = $orderItem->getQtyToShip();
            $item->setQty($qty);
            $shipment->addItem($item);
			$HasItemToShip = true;
        }
		
		if($HasItemToShip == false)
			return false;
			
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
        $order->setStatus('Complete');
        $order->addStatusToHistory($order->getStatus(), 'Order Completed And Shipped Automatically via Automation Routines', false);
        
        $shipment->save();
		
		return true;
}

?>