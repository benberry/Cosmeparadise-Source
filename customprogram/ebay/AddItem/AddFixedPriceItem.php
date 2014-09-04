<?php require_once('../get-common/keys.php') ?>
<?php require_once('../get-common/eBaySession.php') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<TITLE>AddItem</TITLE>
</HEAD>
<BODY>
<FORM action="AddFixedPriceItem.php" method="post">
    <select name="Country">
      <option value="AU">AU</option>
      <option value="UK">UK</option>
      <option SELECTED value="US">US</option>
    </select>        
<INPUT type="submit" name="submit" value="AddItem">
</FORM>


<?php
	if(isset($_POST['Country']))
	{
       /* ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		//Get the item entered
        $listingType     = $_POST['listingType'];
        $primaryCategory = $_POST['primaryCategory'];
        $itemTitle       = $_POST['itemTitle'];
        if(get_magic_quotes_gpc()) {
            // print "stripslashes!!! <br>\n";
            $itemDescription = stripslashes($_POST['itemDescription']);
        } else {
            $itemDescription = $_POST['itemDescription'];
        }
        $itemDescription = $_POST['itemDescription'];
        $listingDuration = $_POST['listingDuration'];
        $startPrice      = $_POST['startPrice'];
        $buyItNowPrice   = $_POST['buyItNowPrice'];
        $quantity        = $_POST['quantity'];
        
        if ($listingType == 'StoresFixedPrice') {
          $buyItNowPrice = 0.0;   // don't have BuyItNow for SIF
          $listingDuration = 'GTC';
        }
        
        if ($listingType == 'Dutch') {
          $buyItNowPrice = 0.0;   // don't have BuyItNow for Dutch
        }*/
        
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		if($_POST['Country'] == 'US')
		{	$siteID = 0;
			$Site = 'US';
			$Currency = "USD";			
			$ShippingService = "EconomyShippingFromOutsideUS";			
			$InternationalShippingServiceOption = '<InternationalShippingServiceOption>
				<ShippingServicePriority>1</ShippingServicePriority>
				<ShippingService>StandardInternational</ShippingService>
				<ShippingServiceCost currencyID="USD">0.00</ShippingServiceCost>		
				<ShippingServiceAdditionalCost currencyID="USD">0.00</ShippingServiceAdditionalCost>		
				<ShipToLocation>Worldwide</ShipToLocation>
			</InternationalShippingServiceOption>';
			$ShipToLocation = "";
			$ExcludeShipToLocation = "<ExcludeShipToLocation>AU</ExcludeShipToLocation><ExcludeShipToLocation>UK</ExcludeShipToLocation>";
			$RefundOption = "<RefundOption>MoneyBackOrExchange</RefundOption>";			
		}else if($_POST['Country'] == 'AU'){
			$siteID = 15;
			$Site = 'Australia';
			$Currency = "AUD";
			$ShippingService = "AU_StandardDeliveryFromOutsideAU";
			$InternationalShippingServiceOption = "";
			$ShipToLocation = "<ShipToLocations>AU</ShipToLocations>";
			$ExcludeShipToLocation = "";
			$RefundOption = "<RefundOption>Exchange</RefundOption>";
		}else{
			$siteID = 3;
			$Site = 'UK';
			$Currency = "GBP";
			$ShippingService = "UK_StandardShippingFromOutside";
			$InternationalShippingServiceOption = "";
			$ShipToLocation = "<ShipToLocations>UK</ShipToLocations>";
			$ExcludeShipToLocation = "";
			$RefundOption = "";
		}
		
		
		//the call being made:
		$verb = 'AddFixedPriceItem';		
		$compatabilityLevel = 661;
		
		///Build the request Xml string		
		$requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
		<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		<RequesterCredentials>
			<eBayAuthToken>'.$userToken.'</eBayAuthToken>
		</RequesterCredentials>
		<ErrorLanguage>en_US</ErrorLanguage>
		<WarningLevel>High</WarningLevel>
		<Item>
			<Title>Sec 7795 - DO NOT BUY</Title>
			<Description>Sec 7795 - DO NOT BUY</Description>
			<PrimaryCategory>
			<CategoryID>78062</CategoryID>
			</PrimaryCategory>
			<StartPrice>500.0</StartPrice>
			<CategoryMappingAllowed>true</CategoryMappingAllowed>
			<ConditionID>1000</ConditionID>
			<Location>HK</Location>
			<Country>HK</Country>
			<Currency>'.$Currency.'</Currency>
			<DispatchTimeMax>3</DispatchTimeMax>
			<ListingDuration>Days_3</ListingDuration>
			<ListingType>FixedPriceItem</ListingType>
			<PaymentMethods>PayPal</PaymentMethods>
			<PayPalEmailAddress>ebay@cosmeparadise.com</PayPalEmailAddress>
			<PictureDetails>
				<PictureURL>http://3.bp.blogspot.com/-bNyFRMPT-Dg/UBfjisxGLuI/AAAAAAAAAaY/AHpGJiu7YsM/s1600/photo+1.JPG</PictureURL>
			</PictureDetails>		
			<Quantity>6</Quantity>
			<ReturnPolicy>
			<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>
			'.$RefundOption.'
			<ReturnsWithinOption>Days_14</ReturnsWithinOption>
			<Description>If you are not satisfied, return the item for refund.</Description>
			<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>
			</ReturnPolicy>
			<BuyerRequirementDetails>
				<ShipToRegistrationCountry>true</ShipToRegistrationCountry>
			</BuyerRequirementDetails>
			<ShippingDetails>
			<ShippingType>Flat</ShippingType>
			<ShippingServiceOptions>
				<ShippingServicePriority>1</ShippingServicePriority>
				<ShippingService>'.$ShippingService.'</ShippingService>
				<FreeShipping>true</FreeShipping>
				<ShippingServiceAdditionalCost currencyID="'.$Currency.'">0.00</ShippingServiceAdditionalCost>				
			</ShippingServiceOptions>
			'.$InternationalShippingServiceOption.'
			'.$ExcludeShipToLocation.'
			</ShippingDetails>
			'.$ShipToLocation.'
			<Site>'.$Site.'</Site>
			<SKU>sosec95</SKU>
		</Item>
		</AddFixedPriceItemRequest>';


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
            $responses = $responseDoc->getElementsByTagName("AddFixedPriceItemResponse");
            foreach ($responses as $response) {
              $acks = $response->getElementsByTagName("Ack");
              $ack   = $acks->item(0)->nodeValue;
              echo "Ack = $ack <BR />\n";   // Success if successful
              
              $endTimes  = $response->getElementsByTagName("EndTime");
              $endTime   = $endTimes->item(0)->nodeValue;
              echo "endTime = $endTime <BR />\n";
              
              $itemIDs  = $response->getElementsByTagName("ItemID");
              $itemID   = $itemIDs->item(0)->nodeValue;
              echo "itemID = $itemID <BR />\n";
              
              //$linkBase = "http://cgi.sandbox.ebay.com/ws/eBayISAPI.dll?ViewItem&item=";
              $linkBase = "http://www.ebay.com/itm/";
              echo "<a href=$linkBase" . $itemID . ">$itemTitle</a> <BR />";
              
              $feeNodes = $responseDoc->getElementsByTagName('Fee');
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
            
            } // foreach response
            
		} // if $errors->length > 0
	}
?>

</BODY>
</HTML>
