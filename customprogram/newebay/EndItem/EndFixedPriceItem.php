<?php require_once('../get-common/keys.php') ?>
<?php require_once('../get-common/eBaySession.php') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<TITLE>EndItem</TITLE>
</HEAD>
<BODY>
<FORM action="EndFixedPriceItem.php" method="post">
    ItemID:<input type="text" name="ItemID" value="<?php echo $_GET['ID']!="" ? $_GET['ID'] : "" ?>" /><br>
End Reason:<select name="EndingReason">
<option value="Incorrect" SELECTED>Incorrect</option>
<option value="LostOrBroken">LostOrBroken</option>
<option value="NotAvailable">NotAvailable</option>
<option value="OtherListingError">OtherListingError</option>
<option value="SellToHighBidder">SellToHighBidder</option>
<option value="Sold">Sold</option>
</select><br>
<INPUT type="submit" name="submit" value="EndItem">
</FORM>


<?php
	if(isset($_POST['ItemID']))
	{    $siteID = 0;
		//the call being made:
		$verb = 'EndFixedPriceItem';		
		$compatabilityLevel = 615;
		
		/////////EndingReason: Incorrect, Sold, NotAvailable, LostOrBroken
		$ItemID = $_POST['ItemID'];
		///Build the request Xml string		
		$requestXmlBody = '
		<?xml version="1.0" encoding="utf-8"?>
		<EndFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		<ItemID>'.$ItemID.'</ItemID>
		<EndingReason>Incorrect</EndingReason>
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
			
		//get any error nodes
		$errors = $responseDoc->getElementsByTagName('Errors');
		
		//if there are error nodes
		if($errors->length > 0)
		{
			echo '<P><B>eBay returned the following error(s):</B>';
			//display each error
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			//Display code and shortmessage
			echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
	
		} else { //no errors            
			//get results nodes
            $responses = $responseDoc->getElementsByTagName("EndFixedPriceItemResponse");
            foreach ($responses as $response) {
              $acks = $response->getElementsByTagName("Ack");
              $ack   = $acks->item(0)->nodeValue;
              echo "Ack = $ack <BR />\n";   // Success if successful
              
              $endTimes  = $response->getElementsByTagName("EndTime");
              $endTime   = $endTimes->item(0)->nodeValue;
              echo "endTime = $endTime <BR />\n";              
              
              echo "$ItemID has ended <BR />\n";
            
            } // foreach response
            
		} // if $errors->length > 0
	}
?>
<p>To <a href="../GetItem/ShowStoreActiveItem.php" target="_self">Check Listing Page</a></p>
</BODY>
</HTML>
