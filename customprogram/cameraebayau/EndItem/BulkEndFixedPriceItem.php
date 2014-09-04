<?php 
require_once('../get-common/keys.php');
require_once('../get-common/eBaySession.php');

if(isset($_FILES["file"])) { 
	if (($_FILES["file"]["type"] == "text/csv")
	|| ($_FILES["file"]["type"] == "application/vnd.ms-excel")
	|| ($_FILES["file"]["type"] == "application/vnd.msexcel")
	|| ($_FILES["file"]["type"] == "application/excel")
	|| ($_FILES["file"]["type"] == "application/x-excel")
	|| ($_FILES["file"]["type"] == "application/x-msexcel")
	|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
	|| ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
	|| ($_FILES["file"]["type"] == "text/comma-separated-values"))
	{
	if ($_FILES["file"]["error"] > 0)
		{   echo "Return Code: " . $_FILES["file"]["error"] . "<br />"; exit;    }
	}
  }
else
  {
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<TITLE>EndItem</TITLE>
</HEAD>
<BODY>
<FORM action="BulkEndFixedPriceItem.php" method="post" enctype="multipart/form-data">
End Reason:<select name="EndingReason">
<option value="Incorrect" SELECTED>Incorrect</option>
<option value="LostOrBroken">LostOrBroken</option>
<option value="NotAvailable">NotAvailable</option>
<option value="OtherListingError">OtherListingError</option>
<option value="SellToHighBidder">SellToHighBidder</option>
<option value="Sold">Sold</option>
</select><br><br>
<label for="file">Upload End Item file(Only SKU):</label><br>
<input type="file" name="file" id="file" /> <br /><br />
<INPUT type="submit" name="submit" value="EndItem">
</FORM>


<?php
 exit;
  }
  
$csvfile = $_FILES["file"]["tmp_name"];

if(!file_exists($csvfile)) {
	echo "File not found.";
	exit;
}

$size = filesize($csvfile);
if(!$size) {
	echo "File is empty.\n";
	exit;
}

if (($handle = fopen($csvfile, "r")) !== FALSE) {	//OPEN CSV
	$row_count = 1;
	$item_count = 0;
	/* Connect to a custom MySQL server */
	$con = mysqli_connect(
	'localhost', /* The host to connect to */
	'cosmepar_program', /* The user to connect as */
	'RecapsBoronWhirlGrands45', /* The password to use */
	'cosmepar_custom'); /* The default database to query */
	
	if (!$con) {
	printf("Can't connect to custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	exit;
	}
	
	$siteID = 0;
	//the call being made:
	$verb = 'EndFixedPriceItem';		
	$compatabilityLevel = 615;
	while (($data = fgetcsv($handle)) !== FALSE) {	//go through data
	$SKU = trim($data[0]);
	
	if($row_count>1){	
		$sql = 'SELECT ItemID FROM `cosme_ebay_listing` WHERE SKU = "'.$SKU.'";';
		//echo $sql;
		$result = mysqli_query($con, $sql);	
		while($row = mysqli_fetch_array($result))
		{	
				
			$ItemID = $row['ItemID']; 
			
			///Build the request Xml string		
			$requestXmlBody = '
			<?xml version="1.0" encoding="utf-8"?>
			<EndFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			<ItemID>'.$ItemID.'</ItemID>
			<EndingReason>'.$_POST['EndingReason'].'</EndingReason>
			<RequesterCredentials>
				<eBayAuthToken>'.$userToken.'</eBayAuthToken>
			</RequesterCredentials>
			</EndFixedPriceItemRequest>';
	
	
			//Create a new eBay session with all details pulled in from included keys.php
			$session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
			
			//send the request and get response
			$responseXml = $session->sendHttpRequest($requestXmlBody);
			if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
				die('<P>Error sending request');
			
			//Xml string is parsed and creates a DOM Document object
			$responseDoc = new DomDocument();
			$responseDoc->loadXML($responseXml);
						
			//if there are error nodes
			//get any error nodes
			$errors = $responseDoc->getElementsByTagName('Errors');
			$ack = $responseDoc->getElementsByTagName('Ack')->item(0)->nodeValue;
			echo "ItemID:".$ItemID." with reason:".$_POST['EndingReason']." update $ack -- ";
			//if there are error nodes
			if($ack == "Failure")
			{			
				$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
				$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
				$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
				//Display code and shortmessage
				echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
				//if there is a long message (ie ErrorLevel=1), display it
				if(count($longMsg) > 0)
					echo '<BR>SKU:'.$SKU."--".str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
		
			} else { //no errors
				echo "SKU:".$SKU." with ItemID:".$ItemID." had ended listing in eBay<br>";
				echo str_repeat(' ',1024*64);
				flush();
				
			} // if $errors->length > 0
			// This is for the buffer achieve the minimum size in order to flush data
			$item_count++;
		}
	}//end if row_count
	$row_count++;
	}//end while loop
	echo "Total end sku:".($row_count-2)." and total item:".$item_count." <br>";
	}//end if handler
?>
<p>To <a href="../GetItem/ShowStoreActiveItem.php" target="_self">Check Listing Page</a></p>
</BODY>
</HTML>
