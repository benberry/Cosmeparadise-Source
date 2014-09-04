<?php require_once('../get-common/keys.php') ?>
<?php require_once('../get-common/eBaySession.php') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<TITLE>AddItem</TITLE>
</HEAD>
<BODY>
<FORM action="AddItem.php" method="post">
<TABLE cellpadding="2" border="0">
	<TR>
		<TD>listingType</TD>
		<TD>
          <select name="listingType">
            <option value="Chinese">Chinese</option>
            <option value="LeadGeneration">Lead Generation format</option>
            <option SELECTED value="FixedPriceItem">Fixed Price Item</option>
            <option value="PersonalOffer">PersonalOffer</option>
          </select>
        </TD>
	</TR>
    <TR>
		<TD>primaryCategory</TD>
		<TD>
          <select name="primaryCategory">
			<option value="14111">Test Category</option>
            <option value="57889">Boys Athletic Pants</option>
            <option value="57890">Boys Corduroys Pants</option>
            <option value="57891">Boys Jeans Pants</option>
            <option value="57892">Boys Khakis Pants</option>
            <option value="177764">Skin Care->Eye Treatments & Masks</option>
            <option SELECTED value="112661">Fragrances->	Unisex</option>
          </select>
        </TD>
	</TR>
    <TR>
		<TD>itemTitle</TD>
		<TD><INPUT type="text" name="itemTitle" value="BERRY 7788 - DO NOT BID" size=30></TD>
	</TR>
    <TR>
		<TD>itemDescription</TD>
		<TD><INPUT type="text" name="itemDescription" value="BERRY 7788 - DO NOT BID" size=30></TD>
	</TR>
    <TR>
	  <TD>listingDuration</TD>
		<TD>
          <select name="listingDuration">
            <option value="Days_1">1 day</option>
            <option value="Days_3">3 days</option>
            <option value="Days_5">5 days</option>
            <option value="Days_7">7 days</option>
          </select>
          (defaults to GTC = 30 days for Store)
        </TD>
	</TR>
    <TR>
		<TD>startPrice</TD>
		<TD><INPUT type="text" name="startPrice" value="<?php echo rand(1,200) / 100 ?>"></TD>
	</TR>
    <TR>
		<TD>buyItNowPrice</TD>
		<TD><INPUT type="text" name="buyItNowPrice" value="<?php echo rand(299,599) / 100; ?>"> (set to 0.0 for Store)</TD>
	</TR>
    <TR>
		<TD>quantity</TD>
		<TD><INPUT type="text" name="quantity" value="1"> (must be 1 for Chinese)</TD>
	</TR>
	<TR>
		<TD colspan="2" align="right"><INPUT type="submit" name="submit" value="AddItem"></TD>
	</TR>
</TABLE>
</FORM>


<?php
	if(isset($_POST['listingType']))
	{
        ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
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
        }
        	
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 0;
		//the call being made:
		$verb = 'AddItem';
		
		$compatabilityLevel = 607;
		
		///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= "<Version>$compatabilityLevel</Version>";
		$requestXmlBody .= '<Item>';
		$requestXmlBody .= '<Site>US</Site>';
		$requestXmlBody .= '<SKU>>USB7788BERRY</SKU>';
		$requestXmlBody .= '<PrimaryCategory>';
		$requestXmlBody .= "<CategoryID>$primaryCategory</CategoryID>";
		$requestXmlBody .= '</PrimaryCategory>';
		$requestXmlBody .= '<CategoryMappingAllowed>true</CategoryMappingAllowed>';
		$requestXmlBody .= "<StartPrice currencyID=\"USD\">$startPrice</StartPrice>";
		$requestXmlBody .= "<BuyItNowPrice currencyID=\"USD\">$buyItNowPrice</BuyItNowPrice>";
		$requestXmlBody .= '<Country>US</Country>';
		$requestXmlBody .= '<Currency>USD</Currency>';
		$requestXmlBody .= '<DispatchTimeMax>3</DispatchTimeMax>';
		$requestXmlBody .= "<ListingDuration>$listingDuration</ListingDuration>";
        $requestXmlBody .= "<ListingType>$listingType</ListingType>";
		$requestXmlBody .= '<Location><![CDATA[San Jose, CA]]></Location>';
		//$requestXmlBody .= '<PaymentMethods>AmEx</PaymentMethods>';
		$requestXmlBody .= "<PaymentMethods>PayPal</PaymentMethods>";
		//$requestXmlBody .= "<PayPalEmailAddress>lailokshing-hk-bs@gmail.com</PayPalEmailAddress>";
		$requestXmlBody .= "<PayPalEmailAddress>ebay@cosmeparadise.com</PayPalEmailAddress>";
		$requestXmlBody .= "<PictureDetails>";
		$requestXmlBody .= "<PictureURL>http://www.cosmeparadise.com/media/catalog/product/ebay/WHAO.jpg</PictureURL>";
		$requestXmlBody .= "</PictureDetails>";
		/*$requestXmlBody .= "<ProductListingDetails>";
		$requestXmlBody .= "<BrandMPN>";
		$requestXmlBody .= "<Brand><![CDATA[Burberry]]></Brand>";
		$requestXmlBody .= "<MPN></MPN>";
		$requestXmlBody .= "</BrandMPN>";
		$requestXmlBody .= "</ProductListingDetails>";	*/	
		$requestXmlBody .= "<Quantity>$quantity</Quantity>";
		//$requestXmlBody .= '<RegionID>0</RegionID>';
		$requestXmlBody .= '<ReturnPolicy>';
		$requestXmlBody .= '<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>';
		$requestXmlBody .= '<RefundOption>MoneyBack</RefundOption>';
		$requestXmlBody .= '<ReturnsWithinOption>Days_30</ReturnsWithinOption>';
		$requestXmlBody .= '<Description>If you are not satisfied, return the book for refund.</Description>';
		$requestXmlBody .= '<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>';
		$requestXmlBody .= '</ReturnPolicy>';		
		$requestXmlBody .= '<ShippingTermsInDescription>True</ShippingTermsInDescription>';
		$requestXmlBody .= '<ShippingDetails>';
		$requestXmlBody .= '<ShippingType>Flat</ShippingType>';
		$requestXmlBody .= '<ShippingServiceOptions>';
		$requestXmlBody .= '<ShippingServicePriority>1</ShippingServicePriority>';
		$requestXmlBody .= '<ShippingService>USPSMedia</ShippingService>';
		$requestXmlBody .= '<ShippingServiceCost>0</ShippingServiceCost>';
		$requestXmlBody .= '</ShippingServiceOptions>';
		$requestXmlBody .= '</ShippingDetails>';
		$requestXmlBody .= "<Title><![CDATA[$itemTitle]]></Title>";
		$requestXmlBody .= "<Description><![CDATA[$itemDescription]]></Description>";
		$requestXmlBody .= '</Item>';
		$requestXmlBody .= '</AddItemRequest>';
		
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
            $responses = $responseDoc->getElementsByTagName("AddItemResponse");
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
