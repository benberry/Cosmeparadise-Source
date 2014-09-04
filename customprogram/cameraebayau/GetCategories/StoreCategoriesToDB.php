<?php set_time_limit(1500) //increase time-out to 25 mins as downloading and parsing the tree may take a while ?>

<?php require_once('../get-common/keys.php') ?>
<?php require_once('../get-common/eBaySession.php') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<TITLE>StoreCategories</TITLE>
</HEAD>
<BODY>

<?php

	/* Connect to a cosmepar_custom MySQL server */
	$con = mysqli_connect(
	'localhost', /* The host to connect to */
	'cosmepar_program', /* The user to connect as */
	'RecapsBoronWhirlGrands45', /* The password to use */
	'cosmepar_custom'); /* The default database to query */
	
	if (!$con) {
	printf("Can't connect to cosmepar_custom MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	exit;
	}
	//SiteID must also be set in the Request's XML
	//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
	//SiteID Indicates the eBay site to associate the call with
	$siteID = 0;
	$compatabilityLevel = 619;
	$item_count=0;
	//Build the request Xml string
	$requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
	$requestXmlBody .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
	$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
	$requestXmlBody .= "<DetailLevel>ReturnAll</DetailLevel>"; //get the entire tree
	$requestXmlBody .= "<Item><Site>$siteID</Site></Item>";
	$requestXmlBody .= "<ViewAllNodes>1</ViewAllNodes>"; //Gets all nodes not just leaf nodes
	$requestXmlBody .= '</GetCategoriesRequest>';
	
	//Create a new eBay session with all details pulled in from included keys.php
	$session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, 'GetCategories');
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
		$responses = $responseDoc->getElementsByTagName("GetCategoriesResponse");
		foreach ($responses as $response) {
		$acks = $response->getElementsByTagName("Ack");
		$ack   = $acks->item(0)->nodeValue;
		echo "Ack = $ack <BR />\n";   // Success if successful
		
		
		$CategoryArray = $response->getElementsByTagName("CategoryArray");
		$Categories = $CategoryArray->item(0)->getElementsByTagName("Category");
		
		foreach($Categories as $Category) {
			$AutoPayEnabled = $Category->getElementsByTagName("AutoPayEnabled")->item(0)->nodeValue;
			$CategoryID = $Category->getElementsByTagName("CategoryID")->item(0)->nodeValue;
			$CategoryLevel = $Category->getElementsByTagName("CategoryLevel")->item(0)->nodeValue;
			$CategoryName = $Category->getElementsByTagName("CategoryName")->item(0)->nodeValue;
			$CategoryParentID = $Category->getElementsByTagName("CategoryParentID")->item(0)->nodeValue;
			$LeafCategory = $Category->getElementsByTagName("LeafCategory")->item(0)->nodeValue;
			
			if($$AutoPayEnabled  == "true")
				$$AutoPayEnabled = 1;
			else
				$$AutoPayEnabled = 0;
				
			if($LeafCategory == null || $LeafCategory == "")
				$LeafCategory = 0;
			else
				$LeafCategory = 1;
			
			$sql = "INSERT INTO ebay_categories (CategoryID,CategoryLevel,CategoryName,CategoryParentID,LeafCategory,AutoPayEnabled) VALUES (".$CategoryID.", ".$CategoryLevel.", '".str_replace("'","''",$CategoryName)."', ".$CategoryParentID.", ".$LeafCategory.", ".$AutoPayEnabled.");";
			echo $sql."<br>";
			try{
				mysqli_query($con, $sql);
			}catch(Exception $e)
			{	echo 'Caught exception: ', $e->getMessage(), "<br>";}
			
			$item_count++;
			}///end item foreach					
		} /// end XML foreach
	}///end  no errors		
	echo "Done. Total Item add:".$item_count;
?>

</BODY>
</HTML>
