<?php
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
	

	$dataPOST = trim(file_get_contents('php://input'));
	$xml = simplexml_load_string($dataPOST);
	$NotificationEventName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->NotificationEventName;
	$BuyerProtection = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->BuyerProtection;
	$Currency = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->Currency;
	$ListingDuration = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ListingDuration;
	$ListingType = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ListingType;
	$ItemID = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ItemID;
	$SKU = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SKU;
	$Quantity = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->Quantity;
	$QuantitySold = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellingStatus->QuantitySold;
	$QuantityAvailable = $Quantity - $QuantitySold;		
	$CurrentPrice = (double)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellingStatus->CurrentPrice;
	$EndTime = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ListingDetails->EndTime;
	$ViewItemURL = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ListingDetails->ViewItemURL;
	$CategoryID = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->PrimaryCategory->CategoryID;
	$CategoryName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->PrimaryCategory->CategoryName;
	$ShipToLocations = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ShipToLocations;
	$Site = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->Site;
	$Title = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->Title;
	$HitCount = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->HitCount;
	$ConditionDisplayName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->ConditionDisplayName;
	$PictureURL = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->PictureDetails->PictureURL;
	
	$ShippingProfileID = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerShippingProfile->ShippingProfileID;
	$ShippingProfileName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerShippingProfile->ShippingProfileName;
	
	$ReturnProfileID = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerReturnProfile->ReturnProfileID;
	$ReturnProfileName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerReturnProfile->ReturnProfileName;
	
	$PaymentProfileID = (int)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerPaymentProfile->PaymentProfileID;
	$PaymentProfileName = (string)$xml->children('soapenv', true)->Body->children()->GetItemResponse->Item->SellerProfiles->SellerPaymentProfile->PaymentProfileName;
		
	if($HitCount == null || $HitCount == "")
		$HitCount = 0;
	if($ConditionID == null || $HitCount == "")
		$ConditionID = 1000;
	if($ShippingProfileID == null || $ShippingProfileID == "")
		$ShippingProfileID = 0;
	if($ReturnProfileID == null || $ReturnProfileID == "")
		$ReturnProfileID = 0;
	if($PaymentProfileID == null || $PaymentProfileID == "")
		$PaymentProfileID = 0;
						
	$Update_Date = "NotificationEventName:".$NotificationEventName."--ItemID:".$ItemID."--SKU:".$SKU."--Quantity:".$Quantity."--QuantitySold:".$QuantitySold."--CurrentPrice:".$CurrentPrice."--EndTime:".$EndTime;
	
	$sql = "";
	if($NotificationEventName == "ItemRevised" || $NotificationEventName == "ItemSold" || $NotificationEventName == "ItemExtended")
	{	$sql = "UPDATE cosme_ebay_listing_2 SET Title ='".str_replace("'","''",$Title)."', QuantityAvailable = ".$QuantityAvailable.", CurrentPrice = ".$CurrentPrice.", EndTime ='".$EndTime."' WHERE ItemId = ".$ItemID;
		
	}else if($NotificationEventName == "ItemListed"){
		$sql = "SELECT COUNT(ItemId) FROM cosme_ebay_listing_2 WHERE ItemId=".$ItemID;
		list($ItemId_COUNT) = mysqli_fetch_row(mysqli_query($con, $sql));
		if($ItemId_COUNT > 0) ////////ItemID exist, it's relist
			$sql = "UPDATE cosme_ebay_listing_2 SET Title ='".str_replace("'","''",$Title)."', QuantityAvailable = ".$QuantityAvailable.", CurrentPrice = ".$CurrentPrice.", EndTime ='".$EndTime."' WHERE ItemId = ".$ItemID;
		else	//////////new listing
			$sql = "INSERT INTO cosme_ebay_listing_2 VALUES ('".str_replace("'","''",$BuyerProtection)."', '".$Currency."', ".$ItemID.", '".str_replace("'","''",$EndTime)."', '".str_replace("'","''",$ViewItemURL)."', '".str_replace("'","''",$ListingDuration)."', '".str_replace("'","''",$ListingType)."', ".$CategoryID.", '".str_replace("'","''",$CategoryName)."', ".$Quantity.", ".$QuantitySold.", ".$QuantityAvailable.", ".$CurrentPrice.", '".str_replace("'","''",$ShipToLocations)."', '".str_replace("'","''",$Site)."', '".str_replace("'","''",$Title)."', '".str_replace("'","''",$SKU)."', ".$HitCount.", ".$ConditionID.", '".str_replace("'","''",$ConditionDisplayName)."', '".str_replace("'","''",$PictureURL)."', ".$ShippingProfileID.", '".str_replace("'","''",$ShippingProfileName)."', ".$ReturnProfileID.", '".str_replace("'","''",$ReturnProfileName)."', ".$PaymentProfileID.", '".str_replace("'","''",$PaymentProfileName)."');";
	
	}else if($NotificationEventName == "ItemClosed"){
		$sql = "DELETE FROM cosme_ebay_listing_2 WHERE ItemId=".$ItemID;
	}else{
		$Update_Date .= "\r\n".$dataPOST;
	}
	
	
	if($sql != "")
	{try{
			mysqli_query($con, $sql);
		}catch(Exception $e)
		{	$error_msr = (string)$e->getMessage();
			$Update_Date .= "\r\n With error message:".$error_msr;
		}
	}
	
	mysqli_close($con);  
	
	 $sCharset = 'utf-8';
	 //$sMailTo = $email;	 
	 $sMailTo = 'support@cosmeparadise.com';	
	 $sMailFrom = 'Cosme Paradise <support@cosmeparadise.com>';
	 // subject
	 $sSubject = "eBay Update Notification";
	 // content
	 $sMessage = $Update_Date." -- \r\n".$sql;
	 
	 /////////////////////SEND EMAIL///////////////////////
		 $sHeaders = "MIME-Version: 1.0\r\n" .
	 			"Content-type: text/html; charset=$sCharset\r\n" .
	 			"From: $sMailFrom\r\n";
	 
	 //send email
	 $mail_sent = @mail($sMailTo, $sSubject, $sMessage, $sHeaders);
	 
	/*if($mail_sent)
		echo "Done, mail sent";
	else
		echo "Fail, mail can't sent";*/
?>