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
	
require_once('../get-common/keys.php');
require_once('../get-common/eBaySession.php');

     ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 15;
		
		//the call being made:
		$verb = 'SetNotificationPreferences';
		$compatabilityLevel = 697;
		
			///Build the request Xml string
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
			$requestXmlBody .= '<SetNotificationPreferencesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
			$requestXmlBody .= '<Version>697</Version>';
			$requestXmlBody .= '<ApplicationDeliveryPreferences>';
			$requestXmlBody .= '<AlertEmail>mailto://support@cosmeparadise.com</AlertEmail>';
			$requestXmlBody .= '<AlertEnable>Enable</AlertEnable>';
			$requestXmlBody .= '<ApplicationEnable>Enable</ApplicationEnable>';
			$requestXmlBody .= '<ApplicationURL>http://www.cosmeparadise.com/customprogram/cosmeticebay/Notif-ItemRevised/Notif-ItemRevised.php</ApplicationURL>';
			$requestXmlBody .= '<DeviceType>Platform</DeviceType>';			
			$requestXmlBody .= '</ApplicationDeliveryPreferences>';		
			$requestXmlBody .= '<UserDeliveryPreferenceArray>';
			//$requestXmlBody .= '<NotificationEnable><EventType>BidReceived</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			//$requestXmlBody .= '<NotificationEnable><EventType>Feedback</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			//$requestXmlBody .= '<NotificationEnable><EventType>EndOfAuction</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '<NotificationEnable><EventType>ItemExtended</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '<NotificationEnable><EventType>ItemListed</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '<NotificationEnable><EventType>ItemRevised</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '<NotificationEnable><EventType>ItemSold</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '<NotificationEnable><EventType>ItemClosed</EventType><EventEnable>Enable</EventEnable></NotificationEnable>';
			$requestXmlBody .= '</UserDeliveryPreferenceArray>';	
			$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '</SetNotificationPreferencesRequest>';
			
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
			
			header('Content-Type: application/xml; charset=utf-8');
			$first_response = $responseDoc->saveXML();
			echo $first_response;		
	
?>
