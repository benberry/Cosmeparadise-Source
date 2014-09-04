<?php 
/////////////////////////start session//////////////////////////////////
session_start(); 

require_once('../get-common/keys.php'); 
require_once('../get-common/eBaySession.php');

$Title = $_POST['Title'];
$SKU = $_POST['SKU'];
$StartPrice = $_POST['StartPrice'];
$Currency = $_POST['Currency'];
$Quantity = $_POST['Quantity'];
$OutOfStockControl = $_POST['OutOfStockControl'];
$PaymentMethods = $_POST['PaymentMethods'];
$PayPalEmailAddress = $_POST['PayPalEmailAddress'];
$Country = $_POST['Country'];
$Location = $_POST['Location'];
$ConditionID = $_POST['ConditionID'];
$ListingType = $_POST['ListingType'];
$ListingDuration = $_POST['ListingDuration'];
$Brand = $_POST['Brand'];
$Volume_Size = $_POST['Volume_Size'];
$Weight = $_POST['Weight'];
$PictureURL = $_POST['PictureURL'];
$Description = $_POST['Description'];
$CategoryID = $_POST['ecatid'];
$ShippingService = $_POST['ShippingService'];
$standard_shipping_cost = $_POST['standard_shipping_cost'];
$standard_additional_cost = $_POST['standard_additional_cost'];
$InternationalShippingService = $_POST['InternationalShippingService'];
$international_shipping_cost = $_POST['international_shipping_cost'];
$international_additional_cost = $_POST['international_additional_cost'];
$Int_ShipToLocation = $_POST['Int_ShipToLocation'];
$ExcludeShipToLocation = $_POST['ExcludeShipToLocation'];
$DispatchTimeMax = $_POST['DispatchTimeMax'];
$ReturnsAcceptedOption = $_POST['ReturnsAcceptedOption'];
$RefundOption = $_POST['RefundOption'];
$ReturnsWithinOption = $_POST['ReturnsWithinOption'];
$ShippingCostPaidByOption = $_POST['ShippingCostPaidByOption'];
$return_policy_desc = $_POST['return_policy_desc'];


	//id	title	mpn	category_name	brand_name	weight	sale_price	img_url	country
	if($Title != "" && $SKU != "" && $StartPrice != "" && $Currency != "" && $Quantity != "" && $OutOfStockControl != "" && $PaymentMethods != "" && $PayPalEmailAddress != "" && $Country != "" && $Location != "" && $ConditionID != "" && $ListingType != "" && $ListingDuration != "" && $PictureURL != "" && $Description != "" && $CategoryID != "" && $ShippingService != "" && $InternationalShippingService != "" && $Int_ShipToLocation != "" && $DispatchTimeMax != "" && $ReturnsAcceptedOption != "" && $RefundOption != "" && $ReturnsWithinOption != "" && $ShippingCostPaidByOption != "" )
	{   //SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 0;  
		if($Currency == "USD")
		{	$siteID = 0;
			$Site = 'US';
			$ShipToLocations = "US";
		}
		if($Currency == "AUD")
		{	$siteID = 15;
			$ShipToLocations = "AU";
			$Site = 'Australia';
		}
		
		$ExcludeShipToLocation = "";
		if($ExcludeShipToLocation != "")
		{	$ExcludeShipToLocation_array = array();
			$ExcludeShipToLocation_array = explode(",", $ExcludeShipToLocation);			
			foreach($ExcludeShipToLocation_array as $ExcludeShipToLocation_value)
			{
				$ExcludeShipToLocation .= "<ExcludeShipToLocation>".trim($ExcludeShipToLocation_value)."</ExcludeShipToLocation>";
			}
		}
		
		$ItemSpecifics = "";
		if($Brand != "" || $Volume_Size != "" || $Weight != "")
		{	$ItemSpecifics .= "<ItemSpecifics>";
			if($Brand != "")
				$ItemSpecifics .= "<NameValueList><Name>Brand</Name><Value>".$Brand."</Value></NameValueList>";
				
			if($Volume_Size != "")
				$ItemSpecifics .= "<NameValueList><Name>Size</Name><Value>".$Volume_Size."</Value></NameValueList>";	
					
			if($Weight != "")
				$ItemSpecifics .= "<NameValueList><Name>Weight</Name><Value>".$Weight."KG</Value></NameValueList>";	
				
			$ItemSpecifics .= "</ItemSpecifics>";
		}	
		
		if($return_policy_desc!="")		
			$return_policy_desc = "<Description>".$return_policy_desc."</Description>";
		//////////////check if free shipping in domestic////////
		$FreeShipping = "";
		if($standard_shipping_cost == 0 && $standard_additional_cost==0)
			$FreeShipping = "true";
		else
			$FreeShipping = "false";
			
		//the call being made:
		$verb = 'AddFixedPriceItem';		
		$compatabilityLevel = 661;
		//<![CDATA[]]>
		$title = $_GET['title'];
		if(strlen($title) > 80)
			$title = substr($title, 0, 75)."...";
		///Build the request Xml string		
		$requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
		<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
		<RequesterCredentials>
			<eBayAuthToken>'.$userToken.'</eBayAuthToken>
		</RequesterCredentials>
		<ErrorLanguage>en_US</ErrorLanguage>
		<WarningLevel>High</WarningLevel>
		<Item>
			<Title><![CDATA['.$Title.']]></Title>
			<Description><![CDATA['.$Description.']]></Description>
			<PrimaryCategory>
			<CategoryID>'.$CategoryID.'</CategoryID>
			</PrimaryCategory>
			<StartPrice>'.$StartPrice.'</StartPrice>
			<CategoryMappingAllowed>true</CategoryMappingAllowed>
			<ConditionID>'.$ConditionID.'</ConditionID>
			<Location>'.$Location.'</Location>
			<Country>'.$Country.'</Country>
			<Currency>'.$Currency.'</Currency>
			<DispatchTimeMax>'.$DispatchTimeMax.'</DispatchTimeMax>
			<ListingDuration>'.$ListingDuration.'</ListingDuration>
			<ListingType>'.$ListingType.'</ListingType>
			<PaymentMethods>'.$PaymentMethods.'</PaymentMethods>
			<PayPalEmailAddress>'.$PayPalEmailAddress.'</PayPalEmailAddress>
			<PictureDetails>
				<PictureURL>'.$PictureURL.'</PictureURL>
			</PictureDetails>		
			<Quantity>'.$Quantity.'</Quantity>
			'.$ItemSpecifics.'			
			<ReturnPolicy>
			<ReturnsAcceptedOption>'.$ReturnsAcceptedOption.'</ReturnsAcceptedOption>
			<RefundOption>'.$RefundOption.'</RefundOption>
			<ReturnsWithinOption>'.$ReturnsWithinOption.'</ReturnsWithinOption>
			'.$return_policy_desc.'
			<ShippingCostPaidByOption>'.$ShippingCostPaidByOption.'</ShippingCostPaidByOption>
			</ReturnPolicy>
			<BuyerRequirementDetails>
				<ShipToRegistrationCountry>true</ShipToRegistrationCountry>
			</BuyerRequirementDetails>
			<ShippingDetails>
			<ShippingType>Flat</ShippingType>
			<ShippingServiceOptions>
				<ShippingServicePriority>1</ShippingServicePriority>
				<ShippingService>'.$ShippingService.'</ShippingService>
				<FreeShipping>'.$FreeShipping.'</FreeShipping>
				<ShippingServiceCost currencyID="'.$Currency.'">'.$standard_shipping_cost.'</ShippingServiceCost>				
				<ShippingServiceAdditionalCost currencyID="'.$Currency.'">'.$standard_additional_cost.'</ShippingServiceAdditionalCost>				
			</ShippingServiceOptions>
			<InternationalShippingServiceOption>
				<ShippingServicePriority>1</ShippingServicePriority>
				<ShippingService>'.$InternationalShippingService.'</ShippingService>
				<ShippingServiceCost currencyID="'.$Currency.'">'.$international_shipping_cost.'</ShippingServiceCost>		
				<ShippingServiceAdditionalCost currencyID="'.$Currency.'">'.$international_additional_cost.'</ShippingServiceAdditionalCost>		
				<ShipToLocation>'.$Int_ShipToLocation.'</ShipToLocation>
			</InternationalShippingServiceOption>
			'.$ExcludeShipToLocation.'
			</ShippingDetails>
			<ShipToLocations>'.$ShipToLocations.'</ShipToLocations>
			<Site>'.$Site.'</Site>
			<SKU>'.$SKU.'</SKU>
		</Item>
		</AddFixedPriceItemRequest>';
		//header('Content-Type: text/xml');
		//echo $requestXmlBody;

        //Create a new eBay session with all details pulled in from included keys.php
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
		
		//send the request and get response
		$responseXml = $session->sendHttpRequest($requestXmlBody);
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
			die('<P>Error sending request');
		
		//Xml string is parsed and creates a DOM Document object
		$responseDoc = new DomDocument();
		$responseDoc->loadXML($responseXml);
		//header('Content-Type: application/xml; charset=utf-8');
		//$first_response = $responseDoc->saveXML();
		//echo $first_response;		
		
		//get any error nodes
		$errors = $responseDoc->getElementsByTagName('Errors');
		$ack = $responseDoc->getElementsByTagName('Ack')->item(0)->nodeValue;
		echo "Ack = $ack <BR />\n";
		//if there are error nodes
		if($errors->length > 0 && $ack == "Failure")
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
			echo 'Error, back to Listing <input type="button" onclick="window.history.back();" value="Preset" />';
		} else { //no errors            
			//get results nodes
            $responses = $responseDoc->getElementsByTagName("AddFixedPriceItemResponse");
            foreach ($responses as $response) {
             
              $endTimes  = $response->getElementsByTagName("EndTime");
              $endTime   = $endTimes->item(0)->nodeValue;
              echo "endTime = $endTime <BR />\n";
              
              $itemIDs  = $response->getElementsByTagName("ItemID");
              $itemID   = $itemIDs->item(0)->nodeValue;
              echo "itemID = $itemID <BR />\n";
              
              //$linkBase = "http://cgi.sandbox.ebay.com/ws/eBayISAPI.dll?ViewItem&item=";
              $linkBase = "http://www.ebay.com/itm/";
              echo "<a href=$linkBase" . $itemID . ">$title</a> <BR />";
              
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
			
			echo 'Done';
			echo '<br>To <a href="../GetItem/ShowStoreActiveItem.php" target="_self">Check Listing</a>';
			echo '<br>To <a href="../ebaycontrol.php" target="_self">Select Combo Page</a>';
		} // check $ack && $errors->length > 0
		
		///////delete session///////
			if(isset($_SESSION['product_id']))
				unset($_SESSION['product_id']);
			//echo 'Done, back to Select <a href="http://www.cosmeparadise.com/customprogram/cameraebay/ebaycontrol.php" target="_self">Page</>';
	}else{
		echo "Some important field(s) missing!<br>";
		echo "title:$Title -- SKU:$SKU";		
	}
	
			///* Connect to a custom MySQL server */
			//$con = mysqli_connect(
			//'localhost', /* The host to connect to */
			//'cosmepar_program', /* The user to connect as */
			//'RecapsBoronWhirlGrands45', /* The password to use */
			//'cosmepar_custom'); /* The default database to query */			
			//if (!$con) {
			//	printf("Can't connect to custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
			//	exit;
			//}		
			//$sql = 'UPDATE ebay_test_data SET '.$ebay_site.'='.$itemID.' WHERE id = '.$_GET['id'];		
			//echo $sql."<br>";		
			///// Send a query to the server
			//mysqli_query($con, $sql);
            
?>
