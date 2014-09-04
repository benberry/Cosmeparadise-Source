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
?>
<html>
<head>
<style>
/*table {border-color:gray;border-spacing:2px;}*/
th {background-color:grey;}
td {padding-left:20px;padding-right:20px;}
.cgrey {background-color:#D8D8D8;}
</style>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript">
    function beforeSubmit(k){
        if (1 == 1){
            //your before submit logic
			//var item1 = "";
			var qty = "";
			for(i=1;i<=k;i++)
			{	qty = $("#qtyEd"+i).val();
				//item1 += qty+"--";
				 $("#BQTYFORM #bqtyid"+i).val(qty);
			}
			//alert("Hello world:"+k+".  Qty of item:"+item1);
			
        }        
        $("#BQTYFORM").submit();   
    }
</script>
</head>
<body>
<h2>Grab ebay price programme by name</h2>
<b>Please type in the sku for search</b>
<form action="GetSellerList.php" method="post" enctype="multipart/form-data">
SKU:<input type="text" name="customlabel" /><br />
<br /><br />
<input type="submit" name="submit" value="Search" />
</form>


<?php 
if($_GET['customlabel'] != "")
{	$CustomLabel = $_GET['customlabel'];
}
else{
	if(($_POST["customlabel"]) != "" && isset($_POST["customlabel"])){
			$CustomLabel = $_POST["customlabel"];			
	}
	else
		exit;
}
echo "<p>search result for SKU:".$CustomLabel."</p>";

require_once('../get-common/keys.php');
require_once('../get-common/eBaySession.php');

		//header('Content-Type: text/xml');
				
        ini_set('magic_quotes_gpc', false);    // magic quotes will only confuse things like escaping apostrophe
		
		//SiteID must also be set in the Request's XML
		//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
		//SiteID Indicates the eBay site to associate the call with
		$siteID = 0;
		//the call being made:
		$verb = 'GetSellerList';
		
		$compatabilityLevel = 549;
		$today =  date("Y-m-d");
		$onedaybefore = date( "Y-m-d", strtotime ("-1 day", strtotime($today))); 
		$fiftydayafter = date( "Y-m-d", strtotime ("+50 day", strtotime($today))); 
		//echo $onedaybefore." and ".$fiftydayafter."<br>";
		
		///Build the request Xml string
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
		$requestXmlBody .= '<EndTimeFrom>'.$onedaybefore.'</EndTimeFrom>';
		$requestXmlBody .= '<EndTimeTo>'.$fiftydayafter.'</EndTimeTo>';
		//$requestXmlBody .= '<StartTimeTo>2014-02-04</StartTimeTo>';
		$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
		$requestXmlBody .= '<Pagination>';
		$requestXmlBody .= '<EntriesPerPage>5</EntriesPerPage>';
		$requestXmlBody .= '<PageNumber>1</PageNumber>';
		$requestXmlBody .= '</Pagination>';
		$requestXmlBody .= '<SKUArray>';
		$requestXmlBody .= '<SKU>'.$CustomLabel.'</SKU>';	//321404
		$requestXmlBody .= '</SKUArray>';
		$requestXmlBody .= '<OutputSelector>ItemArray.Item.Site,ItemArray.Item.SKU,ItemArray.Item.ItemID,ItemArray.Item.Title,ItemArray.Item.Quantity,ItemArray.Item.QuantityAvailableHint,ItemArray.Item.SellingStatus.QuantitySold,ItemArray.Item.Currency,ItemArray.Item.SellingStatus.CurrentPrice,ItemArray.Item.ListingDetails.EndTime</OutputSelector>';
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
            //echo $responseDoc;
			//get results nodes
			$BulkUpdateItems = "";
			$counting = 1;
			echo '<table border="0" cellpadding="0">';
			echo '<thead><tr><th>Ref</th><th>Site</th><th>Title</th><th>SKU</th><th>ItemID</th><th>Currency</th><th>Price</th><th>Qty</th><th>Sold</th><th>End Time</th><th>Edit quantity</th></tr></thead>';
			echo '<tbody>';
            $responses = $responseDoc->getElementsByTagName("GetSellerListResponse");
            foreach ($responses as $response) {
              $acks = $response->getElementsByTagName("Ack");
              $ack   = $acks->item(0)->nodeValue;
              echo "Ack = $ack <BR />\n";   // Success if successful
			 
              $ItemArray = $response->getElementsByTagName("ItemArray");
			  $Items = $ItemArray->item(0)->getElementsByTagName("Item");
			  
              foreach($Items as $Item) {                
				$ItemID	= $Item->getElementsByTagName("ItemID")->item(0)->nodeValue;
				$Currency	= $Item->getElementsByTagName("Currency")->item(0)->nodeValue;
				$SKU	= $Item->getElementsByTagName("SKU")->item(0)->nodeValue;
				$Title	= $Item->getElementsByTagName("Title")->item(0)->nodeValue;
				$Site	= $Item->getElementsByTagName("Site")->item(0)->nodeValue;
				$Quantity	= $Item->getElementsByTagName("Quantity")->item(0)->nodeValue;
				$QuantitySold = $Item->getElementsByTagName("SellingStatus")->item(0)->getElementsByTagName("QuantitySold")->item(0)->nodeValue;
				$QuantityAvailable = $Quantity - $QuantitySold;
                $EndTime = $Item->getElementsByTagName("ListingDetails")->item(0)->getElementsByTagName("EndTime")->item(0)->nodeValue;
                $CurrentPrice = $Item->getElementsByTagName("SellingStatus")->item(0)->getElementsByTagName("CurrentPrice")->item(0)->nodeValue;
				
				$BulkUpdateItems .= '<input type="hidden" name="ItemIDs[]" value="'.$ItemID.'" />';
				$BulkUpdateItems .= '<input type="hidden" name="Qtys[]" value="0" id="bqtyid'.$counting.'" />';
				
				if($counting%2 == 0)
					$classname = "cgrey";
				else
					$classname = "cwhite";
                echo '<tr class="'.$classname.'" id="ref'.$counting.'"><form action="../ReviseItem/ReviseItem.php" method="post" enctype="multipart/form-data">';
				echo "<td>".$counting."</td><td>".$Site."</td><td>".$Title."</td>";
				echo "<td>".$SKU."<input type='hidden' name='customlabel' value='".$SKU."' /></td>";
				echo "<td><a target='_blank' href='http://www.ebay.com/itm/".$ItemID."' title='".$ItemID."'>".$ItemID."</a><input type='hidden' name='ItemID' value='".$ItemID."' /></td>";
				echo "<td>".$Currency."</td><td>".$CurrentPrice."</td>";
				echo '<td><input type="text" id="qtyEd'.$counting.'" name="Qty" value="'.$QuantityAvailable.'" /></td>';
				echo "<td>".$QuantitySold."</td><td>".$EndTime."</td>";
				echo '<td><input type="submit" class="opener" value="Confirm Edit" /></td>';
				echo "</form></tr>";
				$counting++;
				//echo $ItemID." -- ".$Currency." -- ".$SKU." -- ".$Title." -- ".$Site." -- ".$Quantity." -- ".$QuantitySold." -- ".$QuantityAvailable." -- ".$CurrentPrice." -- ".$EndTime."<br>\n";
              } // foreach $ItemArrays
            
            } // foreach response			
            
			//////////////for bulk OS/////////////////
			if($BulkUpdateItems != "")
			{	echo '<tr><td><form action="../ReviseItem/ReviseItem.php" method="post" enctype="multipart/form-data">';
				echo '<input type="hidden" name="customlabel" value="'.$CustomLabel.'" />'.$BulkUpdateItems;
				echo '<input type="submit" value="Bulk O.S" /></form></td></tr>';
			}
			//////////////for bulk QTY update/////////////////
			if($BulkUpdateItems != "")
			{	echo '<tr><td><form id="BQTYFORM" action="../ReviseItem/ReviseItem.php" method="post" enctype="multipart/form-data">';
				echo '<input type="hidden" name="customlabel" value="'.$CustomLabel.'" />'.$BulkUpdateItems;
				echo '<input type="submit" value="Bulk Update Qty" onclick="return beforeSubmit('.($counting-1).');" /></form></td></tr>';
			}
			echo '</tbody>';
			echo "</talbe>";
		} // if $errors->length > 0
		//<a href = '#' onClick="javascript:popUp('http://www.brightpearl.com/support/howto/step-2-of-3-add-skus-to-existing-ebay-listings')"><img src='http://ir.ebaystatic.com/pictures/aw/pics/globalheader/spr11.png'></a>
?>

</body>
</html>