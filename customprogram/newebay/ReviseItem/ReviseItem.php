<?php 

if(isset($_POST['ItemIDs']) && count($_POST['ItemIDs']) > 0 && $_POST['customlabel'] != "" )
{	$CustomLabel = $_POST['customlabel'];
	//$Qty = 0;
	for($i=0; $i< count($_POST['ItemIDs']); $i++)
	{	$ItemID = $_POST['ItemIDs'][$i];
		$Qty = $_POST['Qtys'][$i];
		revise($ItemID,$Qty);
	
	}
}
else if($_POST['customlabel'] != "" && $_POST['ItemID'] != "" && $_POST['Qty'] != "")
{	$CustomLabel = $_POST['customlabel'];
	$ItemID = $_POST['ItemID'];
	$Qty = $_POST['Qty'];
	revise($ItemID,$Qty);
}
else
{	echo "Parameter Incomplete!";
	exit;
}


	function revise($ItemID,$Qty)
	{	require('../get-common/keys.php');
		require_once('../get-common/eBaySession.php');
        ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 0;
		//the call being made:
		$verb = 'ReviseItem';
		
		$compatabilityLevel = "653";
		//$itemTitle = "M.A.C Studio Fix Fluid SPF 15 Foundation(berry test revise!)";
		///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= '<Item>';
		$requestXmlBody .= '<ItemID>'.$ItemID.'</ItemID>';	
		$requestXmlBody .= "<Quantity>$Qty</Quantity>";		
		//$requestXmlBody .= "<Title><![CDATA[$itemTitle]]></Title>";		
		$requestXmlBody .= '</Item>';
		$requestXmlBody .= '</ReviseItemRequest>';
		
        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
		
		//send the request and get response
		$responseXml = $session->sendHttpRequest($requestXmlBody);
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
			die('<P>Error sending request');
		
		//echo "<p><b>$responseXml</b></p><br><Br>";
		
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
            $responses = $responseDoc->getElementsByTagName("ReviseItemResponse");
            foreach ($responses as $response) {
              $acks = $response->getElementsByTagName("Ack");
              $ack   = $acks->item(0)->nodeValue;
              echo "<b>Ack = $ack</b> <BR />\n";   // Success if successful
              
              $endTimes  = $response->getElementsByTagName("EndTime");
              $endTime   = $endTimes->item(0)->nodeValue;
              echo "endTime = $endTime <BR />\n";
              
              $itemIDs  = $response->getElementsByTagName("ItemID");
              $itemID   = $itemIDs->item(0)->nodeValue;
              echo "itemID = $itemID <BR />\n";
              
              /*$linkBase = "http://cgi.sandbox.ebay.com/ws/eBayISAPI.dll?ViewItem&item=";
              echo "<a href=$linkBase" . $itemID . ">$itemTitle</a> <BR />";
              
              $feeNodes = $responseDoc->getElementsByTagName('Fees');
              foreach($feeNodes as $feeNode) {
                $feeNames = $feeNode->getElementsByTagName("Name");
                if ($feeNames->item(0)) {
                    $feeName = $feeNames->item(0)->nodeValue;
                    $fees = $feeNode->getElementsByTagName('Fee');  // get Fee amount nested in Fee
                    $fee = $fees->item(0)->nodeValue;
                    if ($fee > 0.0) {
                        if ($feeName == 'ListingFee') {
                          printf("<B>$feeName : %.2f </B><BR>\n", $fee); 
                        } else {
                          printf("$feeName : %.2f <BR>\n", $fee);
                        }      
                    }  // if $fee > 0
                } // if feeName
              } // foreach $feeNode
            */
            } // foreach response            
		} // if $errors->length > 0
	}
	echo '<br><br><a href="../GetItem/GetSellerList.php?customlabel='.$CustomLabel.'">Back to show SKU</a>';
?>