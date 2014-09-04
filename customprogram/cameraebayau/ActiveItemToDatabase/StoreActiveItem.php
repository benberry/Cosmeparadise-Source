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
if ($ip == "192.240.170.73" ) $accesslist=true;	//us.cosmeparadise.com server
if ($ip == "178.17.36.69" ) $accesslist=true;	//www.cosmeparadise.co.uk server
if ($ip == "61.93.89.10" ) $accesslist=true;	//company IP

if ($accesslist==false) 
	{
	echo $ip;
	exit;
	}
//////////////set default time zone/////////////
	date_default_timezone_set('Asia/Hong_Kong');
	$date1 = new DateTime("now");
	$time_now = $date1->format('Y-m-d H:i:s');
	
	
require_once('../get-common/keys.php');
require_once('../get-common/eBaySession.php');

////////////////////timer//////////////
//$execution_time = microtime(); # Start counting
 $mtime = microtime(); 
   $mtime = explode(" ",$mtime); 
   $mtime = $mtime[1] + $mtime[0]; 
   $starttime = $mtime; 
   
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
	
	$sql = 'TRUNCATE TABLE camau_ebay_listing;';
	mysqli_query($con, $sql);
	
		//header('Content-Type: text/xml');
				
        ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 15;
		//the call being made:
		$verb = 'GetMyeBaySelling';
		
		$compatabilityLevel = 505;
		/*///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<GetMyeBaySellingRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<Version>505</Version>';
		$requestXmlBody .= '<ActiveList>';
		$requestXmlBody .= '<Sort>TimeLeft</Sort>';
		$requestXmlBody .= '<Pagination>';
		$requestXmlBody .= '<EntriesPerPage>200</EntriesPerPage>';
		$requestXmlBody .= '<PageNumber>6</PageNumber>';
		$requestXmlBody .= '</Pagination>';
		$requestXmlBody .= '</ActiveList>';
		$requestXmlBody .= '</GetMyeBaySellingRequest>';*/
		
		//the call being made:
		$verb = 'GetSellerList';
		$compatabilityLevel = 657;
		$today =  date("Y-m-d");
		$onedaybefore = date( "Y-m-d", strtotime ("-1 day", strtotime($today))); 
		$fiftydayafter = date( "Y-m-d", strtotime ("+50 day", strtotime($today))); 		
		
		$TotalNumberOfPages=1;
		$load_count=0;
		$item_count=0;
		echo "ready to Load";
		while( $load_count < $TotalNumberOfPages)
		{	$load_count++;
			///Build the request Xml string
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
			$requestXmlBody .= '<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
			$requestXmlBody .= '<EndTimeFrom>'.$today.'</EndTimeFrom>';
			$requestXmlBody .= '<EndTimeTo>'.$fiftydayafter.'</EndTimeTo>';
			//$requestXmlBody .= '<StartTimeTo>2014-02-04</StartTimeTo>';
			$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
			$requestXmlBody .= '<GranularityLevel>Fine</GranularityLevel> ';//Fine(More) Medium Coarse(less)
			$requestXmlBody .= '<IncludeWatchCount>true</IncludeWatchCount>';
			$requestXmlBody .= '<Pagination>';
			$requestXmlBody .= '<EntriesPerPage>200</EntriesPerPage>';
			$requestXmlBody .= '<PageNumber>'.$load_count.'</PageNumber>';
			$requestXmlBody .= '</Pagination>';		
			$requestXmlBody .= '</GetSellerListRequest>';
			
			//Create a new eBay session with all details pulled in from included keys.php
			$session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
			
			//send the request and get response
			$responseXml = $session->sendHttpRequest($requestXmlBody);
			if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
				die('<P>Error sending request');
			
			
			//echo $responseXml;
			
			//Xml string is parsed and creates a DOM Document object
			$responseDoc = new DomDocument();
			$responseDoc->loadXML($responseXml);
			
			//header('Content-Type: application/xml; charset=utf-8');
			//$first_response = $responseDoc->saveXML();
			//echo $first_response;
				
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
				$responses = $responseDoc->getElementsByTagName("GetSellerListResponse");
				foreach ($responses as $response) {
				$acks = $response->getElementsByTagName("Ack");
				$ack   = $acks->item(0)->nodeValue;
				//echo "Ack = $ack <BR />\n";   // Success if successful
				
				if($load_count==1)
				{	
					$TotalNumberOfPages = $response->getElementsByTagName("PaginationResult")->item(0)->getElementsByTagName("TotalNumberOfPages")->item(0)->nodeValue;
					$TotalNumberOfEntries = $response->getElementsByTagName("PaginationResult")->item(0)->getElementsByTagName("TotalNumberOfEntries")->item(0)->nodeValue;
					//echo "TotalNumberOfPages:".$TotalNumberOfPages." and TotalNumberOfEntries:".$TotalNumberOfEntries."<br>";
				}
				
				$ItemArray = $response->getElementsByTagName("ItemArray");
				$Items = $ItemArray->item(0)->getElementsByTagName("Item");
				
				foreach($Items as $Item) {
					$BuyerProtection = $Item->getElementsByTagName("BuyerProtection")->item(0)->nodeValue;
					//if($BuyerProtection == "ItemIneligible")
					//	continue;
					$Currency = $Item->getElementsByTagName("Currency")->item(0)->nodeValue;
					$ItemID	= $Item->getElementsByTagName("ItemID")->item(0)->nodeValue;
					
					$EndTime = $Item->getElementsByTagName("ListingDetails")->item(0)->getElementsByTagName("EndTime")->item(0)->nodeValue;						
					$date = new DateTime($EndTime);
					$date->add(new DateInterval('PT8H'));
					$new_Date = $date->format('Y-m-d H:i:s');
					if($time_now > $new_Date)
						continue;
					
					$ViewItemURL = $Item->getElementsByTagName("ListingDetails")->item(0)->getElementsByTagName("ViewItemURL")->item(0)->nodeValue;
					$ListingDuration = $Item->getElementsByTagName("ListingDuration")->item(0)->nodeValue;
					$ListingType = $Item->getElementsByTagName("ListingType")->item(0)->nodeValue;				
					$CategoryID = $Item->getElementsByTagName("PrimaryCategory")->item(0)->getElementsByTagName("CategoryID")->item(0)->nodeValue;
					$CategoryName = $Item->getElementsByTagName("PrimaryCategory")->item(0)->getElementsByTagName("CategoryName")->item(0)->nodeValue;
					$Quantity	= $Item->getElementsByTagName("Quantity")->item(0)->nodeValue;
					$QuantitySold = $Item->getElementsByTagName("SellingStatus")->item(0)->getElementsByTagName("QuantitySold")->item(0)->nodeValue;
					$QuantityAvailable = $Quantity - $QuantitySold;			
					$CurrentPrice = $Item->getElementsByTagName("SellingStatus")->item(0)->getElementsByTagName("CurrentPrice")->item(0)->nodeValue;					
					$ShipToLocations = $Item->getElementsByTagName("ShipToLocations")->item(0)->nodeValue;
					$Site	= $Item->getElementsByTagName("Site")->item(0)->nodeValue;
					$Title	= $Item->getElementsByTagName("Title")->item(0)->nodeValue;
					$SKU = $Item->getElementsByTagName("SKU")->item(0)->nodeValue;
					$HitCount = $Item->getElementsByTagName("HitCount")->item(0)->nodeValue;
					$ConditionID = $Item->getElementsByTagName("ConditionID")->item(0)->nodeValue;
					$ConditionDisplayName = $Item->getElementsByTagName("ConditionDisplayName")->item(0)->nodeValue;
					$PictureURL = $Item->getElementsByTagName("PictureDetails")->item(0)->getElementsByTagName("PictureURL")->item(0)->nodeValue;
					
					if($Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerShippingProfile")->length > 0)
					{$ShippingProfileID = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerShippingProfile")->item(0)->getElementsByTagName("ShippingProfileID")->item(0)->nodeValue;
					 $ShippingProfileName = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerShippingProfile")->item(0)->getElementsByTagName("ShippingProfileName")->item(0)->nodeValue;
					}else{
					 $ShippingProfileID = 0;
					 $ShippingProfileName = "";
					}
					
					if($Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerReturnProfile")->length > 0)
					{$ReturnProfileID = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerReturnProfile")->item(0)->getElementsByTagName("ReturnProfileID")->item(0)->nodeValue;
					$ReturnProfileName = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerReturnProfile")->item(0)->getElementsByTagName("ReturnProfileName")->item(0)->nodeValue;
					}else{
					 $ReturnProfileID = 0;
					 $ReturnProfileName = "";
					}
					
					if($Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerPaymentProfile")->length > 0)
					{$PaymentProfileID = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerPaymentProfile")->item(0)->getElementsByTagName("PaymentProfileID")->item(0)->nodeValue;
					$PaymentProfileName = $Item->getElementsByTagName("SellerProfiles")->item(0)->getElementsByTagName("SellerPaymentProfile")->item(0)->getElementsByTagName("PaymentProfileName")->item(0)->nodeValue;
					}else{
					 $PaymentProfileID = 0;
					 $PaymentProfileName = "";
					}
					
					if($HitCount == null || $HitCount == "")
						$HitCount = 0;
					if($ConditionID == null || $HitCount == "")
						$ConditionID = 1000;					
					
					//echo $BuyerProtection."--".$Currency."--".$ItemID."--".$EndTime."--".$ViewItemURL."--".$ListingDuration."--".$ListingType."--".$CategoryID."--".$CategoryName."--".$Quantity."--".$QuantitySold."--".$QuantityAvailable."--".$CurrentPrice."--".$ShipToLocations."--".$Site."--".$Title."--".$SKU."--".$HitCount."--".$ConditionID."--".$ConditionDisplayName."--".$PictureURL."--".$ShippingProfileID."--".$ShippingProfileName."--".$ReturnProfileID."--".$ReturnProfileName."--".$PaymentProfileID."--".$PaymentProfileName."<br><br>";
					
					$sql = "INSERT INTO camau_ebay_listing VALUES ('".str_replace("'","''",$BuyerProtection)."', '".$Currency."', ".$ItemID.", '".str_replace("'","''",$EndTime)."', '".str_replace("'","''",$ViewItemURL)."', '".str_replace("'","''",$ListingDuration)."', '".str_replace("'","''",$ListingType)."', ".$CategoryID.", '".str_replace("'","''",$CategoryName)."', ".$Quantity.", ".$QuantitySold.", ".$QuantityAvailable.", ".$CurrentPrice.", '".str_replace("'","''",$ShipToLocations)."', '".str_replace("'","''",$Site)."', '".str_replace("'","''",$Title)."', '".str_replace("'","''",$SKU)."', ".$HitCount.", ".$ConditionID.", '".str_replace("'","''",$ConditionDisplayName)."', '".str_replace("'","''",$PictureURL)."', ".$ShippingProfileID.", '".str_replace("'","''",$ShippingProfileName)."', ".$ReturnProfileID.", '".str_replace("'","''",$ReturnProfileName)."', ".$PaymentProfileID.", '".str_replace("'","''",$PaymentProfileName)."');";
					//echo $sql."<br>";
					try{
						mysqli_query($con, $sql);
					}catch(Exception $e)
					{	echo 'Caught exception: ', $e->getMessage(), "<br>";}
					
					$item_count++;
					}///end item foreach					
				} /// end XML foreach
			}///end  no errors
		}///end whileloop
		
		echo "item_count: ".$item_count."<br>";
	/////////////////get execution time//////////////
	$mtime = microtime(); 
	$mtime = explode(" ",$mtime); 
	$mtime = $mtime[1] + $mtime[0]; 
	$endtime = $mtime; 
	$totaltime = ($endtime - $starttime); 
	echo "This page was created in ".$totaltime." seconds <br>"; 
?>
